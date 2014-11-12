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
		$banners			=	false,

		/**
		 * Текущий PATH из Url
		 * @var bool
		 */
		$requestUri			=	false,

		/**
		 * Виртуальные категории каталога
		 * @var array
		 */
		$virtuals			=	[],

		/**
		 * Заголовок по умолчанию, если не присваивает в категориях и товарах
		 * @var string
		 */
		$title				=	'',

		/**
		 * Вывод вещей на страницу
		 * @var int
		 */
		$_onpage			=	40;

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

		$this->requestUri	=	$this->request->getURI();

		$path = parse_url($this->requestUri, PHP_URL_PATH);
		$query = parse_url($this->requestUri, PHP_URL_PATH);

		// присваиваю виртуальные категории

		$this->virtuals	=	[
			'/catalogue/sale'		=>	$this->_translate['SALE'],
			'/catalogue/top'		=>	$this->_translate['TOP'],
			'/catalogue/favorites'	=>	$this->_translate['FAVORITES'],
		];

		if(isset($this->virtuals[$path]) && !empty($query))
		{
			// уже ставим заголовок тут для виртуальной категории
			// так как в выдаче товаров при проверке категории, заголовка не будет для виртуалок

			$this->title = $this->virtuals[$path];
		}
	}

	/**
	 * indexAction() По умолчанию главная страница
	 * Должна быть пустая так как она отвечает за оборот экшенов
	 * и служит главным layout для каталога
	 */
	public function indexAction()
	{
		if(isset($this->requestUri)) $action = $this->_helper->catalogueRouteTree($this->requestUri, ['catalogue']);

		if(isset($action->catalogue))
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
				// Выполнить метод и заглушить

				//Получаем массив параметров для фильтрации
				$filter = $this->categoriesModel->parseRemap($this->_shop['id'], $this->request->getQuery(), $this->_onpage);

				//Вывод страницы категории
				$this->_lineItems($filter);
			}
		}
		else
		{
			// На выборку товаров итд итп. Лучше использовать экшн который редиректит на этот index
			// Выполнить метод и заглушить

			//Получаем массив параметров для фильтрации
			$filter = $this->categoriesModel->parseRemap($this->_shop['id'], $this->request->getQuery(), $this->_onpage);

			//Вывод страницы категории
			$this->_lineItems($filter);
		}
	}

	public function itemAction()
	{
		// проверка страницы в кэше

		$content = null;
		if($this->_config->cache->frontend) {
			$content = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));
		}

		if($content === null)
		{
			if($this->request->isGet())
			{
				// Содержимое контроллера для формирования выдачи
				$this->_routeTree = $this->_helper->catalogueRouteTree($this->request->getURI(), [
						'catalogue'
					]);

				// для карточки надо получить последний элемент в url , он и есть артикул
				// /catalogue/88828
				// /catalogue/man/winter-fall/88828

				$articul = end($this->_routeTree->catalogue);

				$item = $this->productsModel->getProductCard($articul, $this->_shop['price_id'], true);

				// передача подходящих размеров для этого товара
				if($item)
				{
					// создание заголовка
					$title = $item['product_name'].' '.$item['brand'];
					$this->tag->prependTitle($title.' - ');

					// Добавляю путь в цепочку навигации
					$this->_breadcrumbs->add($title, $this->request->getURI());

					$sizes = $this->tagsModel->getSizes($item['product_id'], true);

					$this->view->setVars([
						'template'	=>	'item',
						'item' 		=>  $item,
						'sizes' 	=>  $sizes,
						'title' 	=>  $title,
					]);
				}
				$this->view->setVar("categories" , $this->commonModel->categoriesToTree($this->_shopCategories));

				// ссылаюсь на вывод в action index с видом catalogue/index
				$this->view->render('catalogue', 'index')->pick("catalogue/index");
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
			return $this->view->render('catalogue', 'index')->pick("catalogue/index");
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

			$this->view->setVars([
				'template'		=>	'sale',
				"title" 		=> $title,
				"salesGroup" 	=> array_reverse($result, true),
			]);

			$this->view->pick("catalogue/index");
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

			$this->tag->prependTitle($title.' - ');

			// Добавляю путь в цепочку навигации
			$this->_breadcrumbs->add($title, $this->request->getURI());

			// получаю все дочерние категории каталога
			// Получение подкатегорий выбранного магазина с изображением самого рейтингового товара в каждой категории
			// мат. выражение !=, >, <, == ...

			// получаю все дочерние категории каталога
			// Получение подкатегорий выбранного магазина с изображением самого рейтингового товара в каждой категории
			$subCategories = $this->categoriesModel->getCategories($this->_shop['id'], 0, '>', 'ASC', true);

			// Установка заголовка
			$this->tag->prependTitle($this->_translate['TITLE'].' - ');

			// вывожу по умолчанию страницу каталога c вложением subcategories
			$this->view->setVars([
				'template'			=>	'categories',
				'banners'			=>	$this->banners,
				'tree'				=>	$this->_helper->categoriesToTree($this->_shopCategories, 0, true),
				'subcategories'		=>	$this->_helper->arrayToAssoc($subCategories, 'id'),
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

				$subCategories = $this->categoriesModel->getCategories($this->_shop['id'], $this->currentCategory['id'], '=', 'ASC', true);

				// вывожу по умолчанию страницу каталога c вложением subcategories
				$this->view->setVars([
					'template'			=>	'categories',
					'banners'			=>	$this->banners,
					'tree'				=>	$this->_helper->categoriesToTree($this->_shopCategories, 0, true),
					'subcategories'		=>	$this->_helper->arrayToAssoc($subCategories, 'id'),
					'title'				=>	$title,
				]);
			}

			// ссылаюсь на вывод в action index с видом catalogue/index
			$this->view->render('catalogue', 'index')->pick("catalogue/index");
		}
		// Сохраняем вывод в кэш
		if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
	}

	/*
 * Вывод ленты товаров
 * @author <filchakov.denis@gmail.com>
 */
	private function _lineItems($filter = array())
	{
		//удаляем родительскую категорию

		//удаляем родительскую категорию
		if(isset($filter['category'][array_search(1,$filter['category'])])){
			unset($filter['category'][array_search(1,$filter['category'])]);
		}
		if(isset($filter['category'][array_search(2,$filter['category'])])){
			unset($filter['category'][array_search(2,$filter['category'])]);
		}
		if(isset($filter['category']) && count($filter['category'])==0){
			unset($filter['category']);
		}

		$itemLine = $this->categoriesModel->renderItemsLine($filter, $this->_shop['id']);

		// url, категории
		$listingCategories = $this->categoriesModel->getListing($this->_shop['id']);

		foreach($listingCategories as $category)
		{
			if($this->request->getQuery()['_url']	==	$category['url'])
			{
				$title = $category['name'];
				break;
			}

		}

		// устанавливаем заголовок если нашли категорию
		if(!isset($title))  $title = $this->title;
			$this->tag->prependTitle($title.' - ');

		// Добавляю путь в цепочку навигации
		$this->_breadcrumbs->add($title, $this->request->getURI());

		$items = $itemLine['items'];

		unset($itemLine['items']);
		$this->view->setVars([
			'template'   	=> 'itemsline',
			'title'			=> $title,
			'items'      	=> $items,
			'pagination' 	=> $itemLine
		]);

		// ссылаюсь на вывод в action index с видом catalogue/index
		$this->view->pick("catalogue/index");
	}
}

