<?php
namespace Modules\Z95\Controllers;

/**
 * Class CatalogueController Каталог (Карточка товара, вывод из категорий)
 *
 * Доступ к моделям
 *
 * @var $this->shopModel
 * @var $this->productsModel
 * @var $this->categoriesModel
 * @var $this->pricesModel
 *
 * @var $this->_config      доступ ко всем настройкам
 * @var $this->_translate   доступ к переводчику
 * @var $this->_shop        параметры текущего магазина
 * @var $this->_mainCategories главные категории

 *
 * @var $this->di           вызов компонентов из app/config/di.php
 * @var $this->session      вызов сессии
 * @var $this->request      информация об HTTP запросах
 * @var $this->router       посмотреть параметры текущего роута, настроить роуты
 *
 * @var \Helpers\CatalogueTags::catalogueNavTree($request) помошник для построения дерева навигации
 *
 * @package Shop
 * @subpackage Controllers
 */

class CatalogueController extends ControllerBase
{
	private

		/**
		 * Все категории каталога
		 * @var bool
		 */
		$_routeTree = false,

		/**
		 * Текущая категория
		 * @var bool
		 */
		$currentCategory	=	false,

		/**
		 * Баннера для каталога
		 * @var bool
		 */
		$banners			=	false;

	/**
	 * initialize() Инициализирую конструктор
	 * @access public
	 * @return null
	 */
	public function initialize()
	{
		// Загружаю локализацию для контроллера
		$this->loadCustomTrans('catalogue');
		parent::initialize();

		// Заголовок страницы
		$this->tag->setTitle($this->_shop['title']);

		// Получаю баннер для страницы
		$this->banners = $this->bannersModel->getBanners($this->_shop['id'], true);
	}

	/**
	 * indexAction() По умолчанию главная страница
	 * Должна быть пустая так как она отвечает за оборот экшенов
	 * и служит главным layout для каталога
	 */
	public function indexAction()
	{
		$action = $this->_helper->catalogueRouteTree($this->request->getURI(), ['catalogue']);
		if($action->catalogue)
		{
			// если подобран роутинг каталога, считаем количество запрошенных категорий, [0] в конце - каталог всегда первый в URL
			$this->currentCategory = $this->_helper->findInTree($this->_shopCategories, 'alias', $action->catalogue[0]);
			if(sizeof($action->catalogue) == 1 && isset($this->currentCategory[0]))
			{
				$this->currentCategory = current($this->currentCategory);
				// Обработка по адресу /catalogue/{man} существующей категории магазина
				$this->subcategoriesAction();
			}
			else
			{
				// На выборку товаров итд итп. Лучше использовать экшн который редиректит на этот index
				exit('On a sample of the goods, etc. etc.. Better use action that redirect to the index');
			}
		}
	}

	public function itemAction()
	{
		// Установка заголовка
		$this->tag->prependTitle($this->_translate['TITLE'].' - ');

		// проверка страницы в кэше

		$content = null;
		if($this->_config->cache->frontend) {
			$content = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));
		}

		if($content === null) {

			if($this->request->isGet()) {
				// Содержимое контроллера для формирования выдачи
				$this->_routeTree = $this->_helper->catalogueRouteTree($this->request->getURI(), [
						'catalogue'
					]);


				$articul = current($this->_routeTree->catalogue);

				$item = $this->productsModel->getProductCard($articul, $this->_shop['price_id'], true);

				// передача подходящих размеров для этого товара
				$sizes = $this->tagsModel->getSizes($item['product_id'], true);

				$this->view->setVar("sizes", $sizes);
				$this->view->setVar("item", $item);
				$this->view->setVar("categories" , $this->commonModel->categoriesToTree($this->_shopCategories));
			}
		}
			// Сохраняем вывод в кэш
		if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
	}

	/**
	 * Распродажа
	 */
	public function saleAction()
	{
		$queryString = $this->request->getQuery();

		if(isset($queryString['percent']) && isset($queryString['sex']))
		{
			// Формирую заголовок

			if($queryString['percent'][0] > 0)
				$title = $this->_translate['SALE'].' '.$queryString['percent'][0].'%';
			else $title = $this->_translate['SALE'].' - '.$this->_translate['ALL_SALES'];

			$this->tag->prependTitle($title.' - ');

			// Добавляю путь в цепочку навигации
			$this->_breadcrumbs->add($title, $this->request->getURI());

			// вывожу по умолчанию страницу каталога c вложением sales
			$this->view->setVars([
				'template'		=>	'sale',
				'title'			=>	$title,
				'category'      => 	[
					'alias'			=>	'/catalogue/sale',
					'name'			=>	$title,
					'description'	=>	'',
				],
			]);
			// ссылаюсь на вывод в action index с видом catalogue/index
			$this->view->render('catalogue', 'index')->pick("catalogue/index");
		}
		else
		{
			$title = $this->_translate['SALE'];
			$this->tag->prependTitle($this->_translate['SALE'].' - ');

			// Добавляю путь в цепочку навигации
			$this->_breadcrumbs->add($title, $this->request->getURI());

			// получаю колическво товаров по скидкам sex = 1,2....
			$salesGroup = $this->_helper->groupArray(
				$this->pricesModel->countProductsBySales($this->_shop['price_id'], [0,1,2,3], true),
			'sex');

			// удаляю общий подсчет суммы товаров
			array_pop($salesGroup);

			if(isset($salesGroup[0])  && isset($salesGroup[3]))
			{
				// буфер для подсчета 0 - унисекс и 3 детских товаров в скидках
				$temporary = [$salesGroup[0], $salesGroup[3]];

				unset($salesGroup[0], $salesGroup[3]);

				$sum = []; foreach($temporary as $val) {
					foreach($val as $content) {
						@$sum[$content['percent']]	+=	$content['count'];
					}
				}
			}

			// суммирую со всеми скидками

			$result = [];
			foreach($salesGroup as $group => $sales)
			{
				foreach($sales as $sale) {
					if(isset($sum[$sale['percent']]))
					{
						$result[$sale['sex']][] = [
							'percent'	=>	$sale['percent'],
							'count'		=>	$sale['count']+$sum[$sale['percent']]
						];
					}
				}
			}
			$this->view->setVar("salesGroup", $result);
		}
	}

	/**
	 * Категории каталога с выводом изображений по рейтингу товаров
	 */
	public function categoriesAction()
	{
		// проверка страницы в кэше

		$content = null;
		if($this->_config->cache->frontend)
		{
			$content = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));
		}

		if($content === null)
		{
			// Содержимое контроллера для формирования выдачи
			// Формирую заголовок

			$title = $this->_translate['CATALOGUE'];

			// Добавляю путь в цепочку навигации
			$this->_breadcrumbs->add($title, $this->request->getURI());

			// получаю все дочерние категории каталога
			// Получение подкатегорий выбранного магазина с изображением самого рейтингового товара в каждой категории

			$subCategories = $this->categoriesModel->getSubcategories($this->_shop['id'], 0, '>', 'DESC', true);

			// Установка заголовка
			$this->tag->prependTitle($this->_translate['TITLE'].' - ');

			// вывожу по умолчанию страницу каталога c вложением subcategories
			$this->view->setVars([
				'template'			=>	'categories',
				'banners'			=>	$this->banners,
				'subcategories'		=>	$subCategories,
				'title'				=>	$title,
			]);

			// ссылаюсь на вывод в action index с видом catalogue/index
			$this->view->render('catalogue', 'index')->pick("catalogue/index");
		}
		// Сохраняем вывод в кэш
		if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
	}

	/**
	 * Подкатегории каталога с выводом изображений по рейтингу товаров
	 */
	public function subcategoriesAction()
	{

		// проверка страницы в кэше
		$content = null;
		if($this->_config->cache->frontend)
		{
			$content = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));
		}

		if($content === null)
		{
			// Содержимое контроллера для формирования выдачи
			// Формирую заголовок
			if(!empty($this->currentCategory))
			{
				$title = $this->currentCategory['name'];
				$this->tag->prependTitle($title.' - ');

				// Добавляю путь в цепочку навигации
				$this->_breadcrumbs->add($title, $this->request->getURI());

				// получаю все дочерние категории раздела
				// Получение подкатегорий выбранного магазина с изображением самого рейтингового товара в каждой категории

				$subCategories = $this->categoriesModel->getSubcategories($this->_shop['id'], $this->currentCategory['id'], '=', 'DESC', true);

				// вывожу по умолчанию страницу каталога c вложением subcategories
				$this->view->setVars([
					'template'			=>	'categories',
					'banners'			=>	$this->banners,
					'subcategories'		=>	$subCategories,
					'title'				=>	$title,
				]);
			}

			// ссылаюсь на вывод в action index с видом catalogue/index
			$this->view->pick("catalogue/index");
		}
		// Сохраняем вывод в кэш
		if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
	}
}

