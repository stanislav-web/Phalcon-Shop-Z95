<?php
namespace Modules\Z95\Controllers;
use \Helpers\Catalogue,
	\Mappers\Router;

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
 * @package Shop
 * @subpackage Controllers
 */

class CatalogueController extends ControllerBase
{
	private

		/**
		 * Роутинг в виде дерева
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
		$requestUri			=	false;

	/**
	 * initialize() Инициализирую конструктор
	 * @access public
	 * @return null
	 */
	public function initialize()
	{
		parent::initialize();

		// Загружаю локализацию для контроллера
		$this->loadCustomTrans('catalogue');

		// текущий request_uri
		$this->requestUri	=	$this->request->getURI();

		// Заголовок страницы по умолчанию
		$this->tag->setTitle($this->_shop['title']);

		// Получаю баннер для страницы
		$this->banners = $this->bannersModel->getBanners($this->_shop['id'], true);
	}

	/**
 	 * indexAction() По умолчанию главная страница
	 * Должна быть пустая так как она отвечает за оборот экшенов
	 * и служит главным layout для каталога
 	 *
 	 * @access public
 	 * @author Stanislav WEB
 	 * @return null
 	*/
	public function indexAction()
	{
		if(isset($this->requestUri)) $action = Catalogue::catalogueRouteRules($this->requestUri);

		if(isset($action->catalogue))
		{
			// если подобран роутинг каталога, считаем количество запрошенных категорий, [0] в конце - каталог всегда первый в URL
			$this->currentCategory = Catalogue::findInTree($this->_shopCategories, 'alias', $action->catalogue[0]);

			if(sizeof($action->catalogue) == 1 && isset($this->currentCategory[0]))
			{
				$this->currentCategory = current($this->currentCategory);
				// Обработка по адресу /catalogue/{man} существующей категории магазина
				$this->subcategoriesAction();
			}
			else
			{
				// для json рендеринга
				if($this->request->isAjax())
				{
					(new Router())->json()
						->setRules($action)
						//->setShop($this->_shop)
						->setCollection(Catalogue::arrayToAssoc($this->_shopCategories, 'id'))
						->setExclude(['top' => 'ТОП 200', 'favorites' => 'Понравилось', 'new' => 'Новинки', 'sales' => 'Распродажа', 'brands' => 'Бренды'])
						->setNav($this->_breadcrumbs)
						->setTranslate($this->_translate)
						->render($this->productsModel);
				}
				else
				{
					(new Router())
						->setRules($action)
						//->setShop($this->_shop)
						->setCollection(Catalogue::arrayToAssoc($this->_shopCategories, 'id'))
						->setExclude(['top' => 'ТОП 200', 'favorites' => 'Понравилось', 'new' => 'Новинки', 'sales' => 'Распродажа', 'brands' => 'Бренды'])
						->setNav($this->_breadcrumbs)
						->setTranslate($this->_translate)
						->render($this->productsModel);
				}
			}
		}

		// ну и баннер на каждой странице ))
		$this->view->setVar('banners', $this->banners);
	}

	/**
	 * itemAction() Карточка товара
	 *
	 * @access public
	 * @author vavas , Stanislav
	 * @return null
	 */
	public function itemAction()
	{
		if($this->request->isGet())
		{
			// Содержимое контроллера для формирования выдачи
			$this->_routeTree = Catalogue::catalogueRouteRules($this->request->getURI(), [
				'catalogue'
			]);

			// для карточки надо получить последний элемент в url , он и есть артикул
			// /catalogue/88828
			// /catalogue/man/winter-fall/88828

			$articul 	= end($this->_routeTree->catalogue);

			// получаю категорию из которой был получен этот товар (точнее из которой на него попали)
			$alias	=	$this->_routeTree->catalogue[count($this->_routeTree->catalogue)-2];
			$child = Catalogue::findInTree($this->_shopCategories, 'alias', $alias);

			$item = $this->productsModel->getProductCard($articul, $this->_shop['price_id'], true);

			// передача подходящих размеров для этого товара
			if($item)
			{
				// создание заголовка
				$title = $item['product_name'].' '.$item['brand'];
				$this->tag->prependTitle($title.' - ');

				if(!empty($child))
				{
					//@modify Stanislav WEB цепочка крошек до карточки товаров

					// если найдена дочерняя категория товара, ищем ее родителя
					$parent = Catalogue::findInTree($this->_shopCategories, 'id', $child[0]['parent_id']);

					if(!empty($parent)) // добавляю категорию подкатегории
						$this->_breadcrumbs->add($parent[0]['name'], 'catalogue/'.$parent[0]['alias']);

					// добавляю категорию товара
					$this->_breadcrumbs->add($child[0]['name'], 'catalogue/'.$parent[0]['alias'].'/'.$child[0]['alias'])

						// добавляю карточку товара в цепочку навигации
						->add($title, $this->request->getURI());
				}

				// Получаю размеры
				$sizes = $this->tagsModel->getSizes($item['product_id'], true);

				// Определение покупаемых товаров
				$buyModel 	=	(new \Models\BuyTogether())->get(['top_ten'], ['id' => $item['product_id']], [], null, true);

				if(!empty($buyModel))
				{
					// получаю покупаемые с товаром вещи

					$productIds = json_decode($buyModel['top_ten']);

					$buyItems = $this->productsModel->getProductsForBuy($productIds, $this->_shop['price_id'], 6, true);

					$this->view->setVar('buyableItems', $buyItems);
				}

				$this->view->setVars([
					'template'	=>	'item',
					'item' 		=>  $item,
					'sizes' 	=>  $sizes,
					'title' 	=>  $title,
					"discounts"	=>	(!empty($this->_shop['discounts'])) ? json_decode($this->_shop['discounts'], true) : ''
				]);
			}
			$this->view->setVar("categories" , Catalogue::categoriesToTree($this->_shopCategories));

			// ссылаюсь на вывод в action index с видом catalogue/index
			$this->view->render('catalogue', 'index')->pick("catalogue/index");
		}
	}

	/**
	 * brandsAction() Страница всех брендов
	 *
	 * @see /brands
	 * @author <filchakov.denis@gmail.com>
	 * @modify Stanislav WEB
	 */
	public function brandsAction()
	{
		$this->tag->appendTitle(' - '.$this->_translate['ALL_BRANDS']);

		$arrayBrand = Catalogue::arrayToAssoc((array)$this->brandsModel->getAllBrands($this->_shop['id']), 'name');
		$result = '';

		foreach($arrayBrand as $name => $infoBrand){
			$key = strtolower($name[0]);
			$result[$key][] = $infoBrand;
		}

		$this->view->setVars([
			'title'	=> $this->_translate['ALL_BRANDS'],
			'template'		=>	'brands',
			'brands' 		=> $result,
			'banners'		=> $this->banners
		]);

		$this->view->pick("catalogue/index");
	}

	/*
 	 * favoritesAction()  Избранные вещи
 	 *
 	 * @see /catalogue/favorites
 	 * @access public
 	 * @author Stanislav WEB
 	 * @return \Phalcon\Mvc\View -> render()
 	 */
	public function favoritesAction()
	{
		$title = $this->_translate['FAVORITES'];
		$this->tag->prependTitle($this->_translate['FAVORITES'].' - ');

		// Добавляю путь в цепочку навигации
		$this->_breadcrumbs->add($title, $this->request->getURI());

		$favorites = $this->session->get('favorites');
		if(isset($favorites) && !empty($favorites))
		{
			$favorites = array_keys($favorites);

			// собираю чистый запрос на конструкторе
			$items = $this->productsModel->get([
				'prod.id', 'prod.name', 'prod.articul', 'prod.preview', 'price.price', 'price.discount', 'brand.name as brand_name', 'filter_size'
			],
			['prod.id' => $favorites, 'price.id' => $this->_shop['price_id']], ['id' =>'ASC'], sizeof($favorites), true);
		}

		$this->view->setVars([
			'template'	=>	'itemsline',
			'title' 	=> 	$title,
			'items' 	=> 	(sizeof($favorites) == 1)  ? [$items] : $items,
			'count'		=>	sizeof($favorites),
			'favorites'	=>	$favorites,
			'banners'	=> $this->banners
		]);

		$this->view->pick("catalogue/index");
	}

	/*
	 * saleAction() Лента скидок. Метод выполняется одновременно как action
	 * но в случае передачи параметров queryString перекидывает на выдачу
	 *
	 * @see /catalogue/sale
	 * @access public
	 * @author Stanislav WEB
	 * @return \Phalcon\Mvc\View -> render()
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
			// Формирую заголовок

			$title = $this->_translate['SALE'];
			$this->tag->prependTitle($this->_translate['SALE'].' - ');

			// Добавляю путь в цепочку навигации
			$this->_breadcrumbs->add($title, $this->request->getURI());

			// получаю колическво товаров по скидкам sex = 1,2....
			$salesGroup = Catalogue::groupArray(
				$this->pricesModel->countProductsBySales($this->_shop['price_id'], [0,1,2,3], true),
				'sex');

			// удаляю общий подсчет суммы товаров
			array_pop($salesGroup);

			if(isset($salesGroup[0])  && isset($salesGroup[3]))
			{
				// буфер для подсчета 0 - унисекс и 3 детских товаров в скидках
				$temporary = [$salesGroup[0], $salesGroup[3]];

				unset($salesGroup[0], $salesGroup[3]);

				$sum = [];
				foreach($temporary as $val) {
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
				"salesGroup" 	=> $result,
				'banners'		=> $this->banners
			]);

			$this->view->pick("catalogue/index");
		}
	}

	/*
 	 * categoriesAction() Категории каталога с выводом изображений по рейтингу товаров
	 *
 	 * @see /catalogue
 	 * @access public
 	 * @author Stanislav WEB
 	 * @return \Phalcon\Mvc\View -> render()
 	 */
	public function categoriesAction()
	{
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
			'tree'				=>	Catalogue::categoriesToTree($this->_shopCategories, 0, true),
			'subcategories'		=>	Catalogue::arrayToAssoc($subCategories, 'id'),
			'title'				=>	$title,
		]);

		// ссылаюсь на вывод в action index с видом catalogue/index
		$this->view->render('catalogue', 'index')->pick("catalogue/index");
	}

	/*
  	 * subcategoriesAction() Подкатегории каталога с выводом изображений по рейтингу товаров
 	 *
  	 * @see /catalogue/{main_category}
  	 * @access public
  	 * @author Stanislav WEB
  	 * @return \Phalcon\Mvc\View -> render()
  	 */
	public function subcategoriesAction()
	{
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
				'tree'				=>	Catalogue::categoriesToTree($this->_shopCategories, 0, true),
				'subcategories'		=>	Catalogue::arrayToAssoc($subCategories, 'id'),
				'title'				=>	$title,
			]);
		}

		// ссылаюсь на вывод в action index с видом catalogue/index
		$this->view->render('catalogue', 'index')->pick("catalogue/index");
	}
}

