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
		 * Лимит вывода товаров на страницу
		 * @var int
		 */
		$_onpage = 10;

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
		$this->tag->setTitle($this->_shop->title);
	}

	/**
	 * indexAction() По умолчанию главная страница
	 * Маршрутизация
	 * @example /catalogue
	 *          /catalogue/woman
	 *          /catalogue/woman/skirt
	 *
	 * @example /catalogue/woman/skirt/tags/under/bottom/small/brands/Dolche/Louis%20Vuton
	 * @access public
	 */
	public function indexAction()
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

			if($this->request->isGet())
			{
				// Определение роутинга
				$this->_routeTree = $this->_helper->catalogueRouteTree($this->request->getQuery()['_url'], [
					'catalogue', 'brands', 'tags'
				]);

				// Работа с категориями и тегами
				if(isset($this->_routeTree['catalogue'])) 	$this->_categories();

				// Работа с брендами
				//if(isset($this->_routeTree['brands']))		$this->_brands();
			}
		}
		// Сохраняем вывод в кэш
		if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
	}

	public function itemAction()
	{
		// Установка заголовка
		$this->tag->appendTitle('- '.$this->_translate['TITLE']);


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

				$articul = current($this->_routeTree['catalogue']);

				$item = $this->productsModel->getProductCard($articul, $this->_shop->price_id, true);

				// передача подходящих размеров для этого товара
				$sizes = $this->tagsModel->getSizes($item->product_id, true);

				$this->view->setVar("sizes", $sizes);
				$this->view->setVar("item", $item);
				$this->view->setVar("categories" , $this->commonModel->categoriesToTree($this->_shopCategories));
			}

		}
			// Сохраняем вывод в кэш
		if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
	}

	/**
	 * Обработка загрузки категорий, подкатегорий и товаров, навигация по тегам
	 * @access private
	 */
	private function _categories()
	{
		// Обработка и показ категорий
		if(empty($this->_routeTree['catalogue']))
		{
			$this->_shopCategories = $this->_helper->arrayToAssoc($this->_shopCategories, 'id');

			// Установка заголовка
			$this->tag->appendTitle('- '.$this->_translate['TITLE']);

			// Вывод в шаблон
			$this->view->setVars([
				'template'		=>	'categories',
				'categoriesSide'=>	$this->_helper->arrayToAssoc((array)$this->_shopMainCategories, 'id'),
			]);
		}

		// Обработка и показ подкатегорий
		elseif(sizeof($this->_routeTree['catalogue']) == 1)
		{
			// если главная категория, ищу ее в списке категорий
			$category = $this->_helper->findInTree($this->_shopCategories, 'alias', $this->_routeTree['catalogue'][0]);

			// получаю скписок дочерних категорий от $category
			$subCategories = $this->_helper->arrayToAssoc(
				$this->_helper->findInTree($this->_shopCategories, 'parent_id', array_values($category)[0]['id']),
				'id'
			);

			if(!empty($subCategories))
			{
				// подсчета товаров в подкатегориях
				$productsCount = $this->_helper->arrayToAssoc($this->commonModel->getCountProducts(array_keys($subCategories), true), 'id');
				$this->session->set('productsCount', $productsCount);
			}

			// Установка заголовка
			$this->tag->appendTitle('- '.$category[0]['name']);

			// Вывод в шаблон
			$this->view->setVars([
				'template'		=>	'subcategories',
				'category'      => 	array_values($category)[0],
				'categoriesSide'=> 	(isset($productsCount)) ? array_intersect_key($subCategories, $productsCount) : [],
				'count'         => 	(isset($productsCount)) ? $this->_helper->arrayToAssoc($productsCount, 'id') : 0,
				'allcount'      => 	array_sum(array_map(function ($item) {
					return $item['product_count'];
				}, $this->_helper->objectToArray($productsCount)))
			]);
		}
		else
		{
			// вывод товаров в подкатегории. переадресовую на обработку другого action

			$currentPage = abs($this->request->getQuery('page', 'int', 0));
			if($currentPage == 0) {
				$offSet = 0;
				$currentPage = 1;
			}
			else $offSet = $currentPage*$this->_onpage;

			// Получаю параметры категорий и подкатегорий
			$category = $this->_helper->findInTree($this->_shopCategories, 'alias', end($this->_routeTree['catalogue']));

			if(!empty($category))
			{
				$categoriesSide = $this->_helper->arrayToAssoc($this->_helper->categoriesToTree($this->_shopCategories, $category[0]['parent_id']), 'id');

				// проверяю, есть ли теги в роутинге беру их ID
				if(isset($this->_routeTree['tags']))
				{
					$tags = $this->tagsModel->get(['id', 'alias'], ['alias' => $this->_routeTree['tags']], array(), null, true);
					$tagIds = array_keys($this->_helper->arrayToAssoc($tags, 'id'));

					// для подсветки выбранных тегов
					$this->view->setVar('tagactive', array_keys($this->_helper->arrayToAssoc($tags, 'alias')));
				}

				// Получаю параметры това	ров (фильтрующий запрос)

				$products = $this->productsModel->getProducts(
					$this->_shop->price_id, 						// параметр цены магазина
					isset($this->_routeTree['tags']) ? array(
						'rel.tag_id' => $tagIds						// параметры тегов
					) : array(
						'rel.category_id' => $category[0]['id']		// параметры товаров
					),
					$offSet,										// начальная позиция выборки
					$this->_onpage,									// лимит выборки
					true);
				$pageProductsCount = (isset($products['count'])) ? $products['count'] : 0;
				$products = $this->_helper->arrayToAssoc($products, 'id');

				// получаю все теги в категории по всем товарам

				$tags = $this->tagsModel->getByProductIds($category[0]['id'], true);

				$this->view->setVar('tags', $tags);

				// Получаю товары, бренды и их теги по категории
				// Вывод в шаблон
				$this->view->setVars([
					'pager'			=>	array(
						'onpage'	=>	$this->_onpage,
						'current'	=>	$currentPage,
						'allpages'	=>	ceil($pageProductsCount/$this->_onpage),
						'items'		=>	$pageProductsCount,
						'offset'	=>	$offSet,
						'show'		=>	$offSet+($currentPage*$this->_onpage),
					),
					'allcount'			=>	$pageProductsCount,
					'category'      	=> 	$category[0],
					'categoriesSide'	=>	$categoriesSide,
					'items'				=>  $products,
					'count'				=>	$this->session->get('productsCount'),
					'template'			=>	'products',
				]);
			}
		}
	}
}

