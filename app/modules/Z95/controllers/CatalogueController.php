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
				// ВСЕ что сюда переходит и есть выдача товаров
				(new Router())	->setRules($action)
								->setShop($this->_shop)
								->setCollection(Catalogue::arrayToAssoc($this->_shopCategories, 'alias'))
								->setExclude(['top' => 'ТОП 200', 'favorites' => 'Понравилось', 'new' => 'Новинки', 'sale' => 'Распродажа'])
								->setNav($this->_breadcrumbs)
								->render($this->productsModel);
			}
		}
	}

	/**
	 * itemAction() Карточка товара
	 *
	 * @access public
	 * @author vavas , Stanislav §
	 * @return null
	 */
	public function itemAction()
	{
		// проверка страницы в кэше

		$content = null;

		if($this->_config->cache->frontend)
			$content = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));

		if($content === null)
		{
			if($this->request->isGet())
			{
				// Содержимое контроллера для формирования выдачи
				$this->_routeTree = Catalogue::catalogueRouteTree($this->request->getURI(), [
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

						$this->_breadcrumbs
							// добавляю категорию подкатегории
							->add($parent[0]['name'], 'catalogue/'.$parent[0]['alias'])

							// добавляю категорию товара
							->add($child[0]['name'], 'catalogue/'.$parent[0]['alias'].'/'.$child[0]['alias'])

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

						$buyItems = $this->productsModel->getProductsForBuy($productIds, $this->_shop['price_id'], 8, true);

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
				$this->view->setVar("categories" , $this->commonModel->categoriesToTree($this->_shopCategories));

				// ссылаюсь на вывод в action index с видом catalogue/index
				$this->view->render('catalogue', 'index')->pick("catalogue/index");
			}
		}
			// Сохраняем вывод в кэш
		if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
	}

	/**
	 * Страница всех брендов
	 * @author <filchakov.denis@gmail.com>
	 */
	public function brandsAction(){
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
			'brands' => $result,
		]);

		return $this->view->render('catalogue', 'index')->pick("catalogue/index");
	}

	/*
	 * Страница TOP200
	 * @author <filchakov.denis@gmail.com>
	 */
	function topAction(){
		$filter = $this->categoriesModel->parseRemap($this->_shop['id'], $this->request->getQuery(), $this->_onpage);

		// создание заголовка
		$title = $this->_translate['TOP'];
		$this->tag->setTitle($title.' - '.$this->_translate['CATALOGUE']);

		// Добавляю путь в цепочку навигации
		$this->_breadcrumbs->add($title, $this->request->getURI());

		//$filter['top'] = true;
		$this->_lineItems($filter);
		$this->view->setVar('title', $title);
		$this->view->render('catalogue', 'index')->pick("catalogue/index");
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
		// проверка страницы в кэше

		$content = null;
		if($this->_config->cache->frontend)
			$content = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));

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
				'tree'				=>	Catalogue::categoriesToTree($this->_shopCategories, 0, true),
				'subcategories'		=>	Catalogue::arrayToAssoc($subCategories, 'id'),
				'title'				=>	$title,
			]);

			// ссылаюсь на вывод в action index с видом catalogue/index
			$this->view->render('catalogue', 'index')->pick("catalogue/index");
		}
		// Сохраняем вывод в кэш
		if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
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
		// проверка страницы в кэше
		$content = null;
		if($this->_config->cache->frontend)
			$content = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));

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
					'tree'				=>	Catalogue::categoriesToTree($this->_shopCategories, 0, true),
					'subcategories'		=>	Catalogue::arrayToAssoc($subCategories, 'id'),
					'title'				=>	$title,
				]);
			}

			// ссылаюсь на вывод в action index с видом catalogue/index
			$this->view->render('catalogue', 'index')->pick("catalogue/index");
		}
		// Сохраняем вывод в кэш
		if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
	}

	/**
	 * Фильтрация товаров согласно новым условиям
	 * @author <filchakov.denis@gmail.com>
	 */
	function filtration(){


		$oldRules = $this->categoriesModel->parseRemap($this->_shop['id'], $_REQUEST, $this->_onpage);
		$newUrl['_url'] = implode('/', $_REQUEST['tags']);

		$newRules = $this->categoriesModel->parseRemap($this->_shop['id'], $newUrl, $this->_onpage);

		if(isset($oldRules['new']) && $oldRules['new']==true){$newRules['new'] = true;}
		if(isset($oldRules['sex'])){$newRules['sex'] = $oldRules['sex'];}
		if(isset($oldRules['top']) && $oldRules['top']==true){$newRules['top'] = true;}
		if(isset($oldRules['category'])){$newRules['category'] = $oldRules['category']; }

		$newRules['page'] = 0;

		$filter = $newRules;

		$itemLine = $this->categoriesModel->renderItemsLine($filter, $this->_shop['id']);
		$items = $itemLine['items'];

		unset($itemLine['items']);

		$result['url'] = $this->categoriesModel->buildUrl($filter);

		$_REQUEST['_url'] = $result['url'];

		if($items < ($itemLine['limit']*$itemLine['page'])){
			$itemLine['page'] = 1;
		}

		$result['url'] = $result['url'];

		$view = new \Phalcon\Mvc\View\Simple();
		$view->setViewsDir("../app/modules/".$this->_shop['code']."/views/");
		$result['html'] = $view->render("partials/catalogue/itemsline",
			array(
				'items'      	=> $items,
				'pagination' 	=> $itemLine,
				'viewTranslate' => $this->_translate,
				'shop'			=>	$this->_shop,
				'itemsline'		=>	$itemLine,
			));

		echo json_encode($result);die;
	}

	/**
	 * Возвращает рекомендации
	 */
	function getRecommendedItems(){
		$ids = explode(',', $_REQUEST['ids']);
		$infoIds = $this->productsModel->getRecommend($ids);
		$result['html'] = 'Здесь будут товары с ID — '.$infoIds;
		return $result;
	}

	/**
	 * Генерация сайдбара
	 * @author <filchakov.denis@gmail.com>
	 * @return array
	 */
	function filterSidebar (){
		$filter = $this->categoriesModel->parseRemap($this->_shop['id'], $this->request->getQuery(), $this->_onpage);

		$sidebar = $this->categoriesModel->renderFilter($filter, $this->_shop['id']);

		if($sidebar!=''){

			$urlFilter = $filter;
			if(isset($urlFilter['tags'])){
				unset($urlFilter['tags']);
			}

			if(isset($urlFilter['price'])){
				unset($urlFilter['price']);
			}
			$urlFilter['page'] = 0;
			$urlClear = $this->categoriesModel->buildUrl($urlFilter);

			$sidebar = '<a class="reset" href="/catalogue/'.$urlClear.'">Сбросить все</a>'.$sidebar;
		}

		if($sidebar==''){
			return array('html'=>'');
		}

		$result = '<form id="filters" onsubmit="return false">
						<div class="tags-filter Shadow">
						<div class="close" onclick="$(this).parent().toggleClass(\'hidden\'); $(\'#tags_filter_button\').removeClass(\'hidden\');" title="Закрыть фильтры"></div>
							<div class="filters">
								';
		$result .= $sidebar;
		$result .= '
							</div>
						</div>
					</form>
					<script>
						$("#filters").change(function(event){
							global.showStatus("common.loading");

							if($(\'body\').scrollTop()>500){
								$(\'html, body\').animate({
									scrollTop: $("#CONTENT").offset().top
								}, 1000);
							}

							event.preventDefault();
							$.ajax({
								type: "GET",
								url: window.location.href,
								data: "ajax=filtration&"+$(this).serialize(),
								dataType: "json",
								success: function (ajax) {
									global.hideStatus("common.loading");
									window.history.pushState("", "", "/catalogue/"+ajax.url);
									global.hideStatus(\'common.loading\');

									$(".element-catalogue_items").html(ajax.html);
								}
							});
						});
					</script>';


		return array('html'=>$result, 'url'=>json_encode($filter));
	}

	/*
	 * Страница новинок
	 * @author <filchakov.denis@gmail.com>
	 */
	function newAction(){
		$filter = $this->categoriesModel->parseRemap($this->_shop['id'], $this->request->getQuery(), $this->_onpage);
		//удаляем родительскую категорию
		//$filter['new'] = 1;
		$this->_lineItems($filter);
		$title = $this->_translate['NEW_CATEGORY'];
		$this->_breadcrumbs->add($title, $this->request->getURI());
		$this->view->render('catalogue', 'index')->pick("catalogue/index");
	}

	/**
	 * Определение крошек по результату выборки
	 * @param array $filter
	 */
	private function _breadTitle($filter = array()){
		if(isset($filter['brand']) && !isset($filter['category'])){
			$result = $this->brandsModel->get(array('id'=>current($filter['brand'])))['name'];
		}
		return $result;
	}

	/*
	 * Вывод ленты товаров
	 * @author <filchakov.denis@gmail.com>
	 */
	private function _lineItems($filter = array()){

		//удаляем родительскую категорию
		if(isset($filter['top'])){
			$itemLine = $this->categoriesModel->renderTopItemsLine($filter, $this->_shop['id']);
		} else {
			$itemLine = $this->categoriesModel->renderItemsLine($filter, $this->_shop['id']);
		}

		foreach($this->categoriesModel->getListing($this->_shop['id']) as $category){
			$arrayUrl = explode('/',$this->request->getQuery()['_url']);
			$arrayCategory = explode('/',$category['url']);
			if(count(array_intersect($arrayCategory,$arrayUrl))==count($arrayCategory)){
				$pagetitle = $category['name'];
			}
		}

		//Перезаписываем название страницы
		if($pagetitle==''){
			$pagetitle = $this->_breadTitle($filter);
		}

		$title = $this->_translate['CATALOGUE'];
		$this->tag->prependTitle($title.' - ');

		// Добавляю путь в цепочку навигации
		$this->_breadcrumbs->add($pagetitle, $this->request->getURI());

		$items = $itemLine['items'];

		unset($itemLine['items']);
		$this->view->setVars([
			'template'   => 'itemsline',
			'title'		=> $pagetitle,
			'items'      => $items,
			'pagination' => $itemLine,
			'itemsline' => array_keys(Catalogue::arrayToAssoc($items,'id')),
		]);

		$this->view->pick("catalogue/index");
	}

}

