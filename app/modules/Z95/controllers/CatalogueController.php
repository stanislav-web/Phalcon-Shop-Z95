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

		$requestUri			=	false,

		$virtuals			=	['/catalogue/sale', '/catalogue/top', '/catalogue/favorites', '/catalogue/apple'];

		//Лимит вывода товаров на страницу
		private $_onpage = 100;

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

		//Обработка AJAX запросов
		if ($this->request->isAjax() == true) {

			if($this->request->get('ajax')){
				$method = $this->request->get('ajax');
			}
			$this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
			$result = $this->{$method}();
			$result = json_encode($result);
			exit($result);
		}

		$this->requestUri	=	$this->request->getURI();

		// Заголовок страницы
		$this->tag->setTitle($this->_shop['title']);

		// Получаю баннер для страницы
		$this->banners = $this->bannersModel->getBanners($this->_shop['id'], true);

		$path = parse_url($this->request->getURI(), PHP_URL_PATH);
		$query = parse_url($this->request->getURI(), PHP_URL_PATH);

		if(in_array($path, $this->virtuals) && !empty($query))
		{
			$this->requestUri = false;
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
				//Получаем массив параметров для фильтрации
				$filter = $this->categoriesModel->parseRemap($this->_shop['id'], $this->request->getQuery(), $this->_onpage);
				//Проверка на вызов метода.
				if(isset($filter['category']) || count($filter)>4){
					//Вывод страницы категории
					$this->_lineItems($filter);
				}
			}
		}
		else
		{
			//Получаем массив параметров для фильтрации
			$filter = $this->categoriesModel->parseRemap($this->_shop['id'], $this->request->getQuery(), $this->_onpage);
			//Проверка на вызов метода.
			if(isset($filter['category']) || count($filter)>4){
				//Вывод страницы категории
				$this->_lineItems($filter);
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
				if($item) {
					$sizes = $this->tagsModel->getSizes($item['product_id'], true);
					$this->view->setVar("item", $item);
				}

				$this->view->setVar("sizes", $sizes);

				$this->view->setVar("categories" , $this->commonModel->categoriesToTree($this->_shopCategories));
			}
		}
			// Сохраняем вывод в кэш
		if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
	}

	/**
	 * Вывод страницы всех брендов
	 * @author <filchakov.denis@gmail.com>
	 */
	public function brandsAction(){
		$title = $this->_translate['ALL_BRANDS'];
		$this->tag->prependTitle($this->_translate['ALL_BRANDS'].' - ');

		// Добавляю путь в цепочку навигации
		$this->_breadcrumbs->add($title, $this->request->getURI());

		$arrayBrand = $this->_helper->arrayToAssoc((array)$this->brandsModel->getAllBrands($this->_shop['id']), 'name');
		$result = '';
		foreach($arrayBrand as $name => $infoBrand){
			$key = strtolower($name[0]);
			$result[$key][] = $infoBrand;
		}

		$this->view->setVars([
			'template'		=>	'brands',
			'title' => $title,
			'brands' => $result
		]);

		$this->view->pick("catalogue/index");
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
				"salesGroup" 	=> $result,
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

		if(isset($oldRules['sex'])){
			$newRules['sex'] = $oldRules['sex'];
		}

		if(isset($oldRules['category'])){
			$newRules['category'] = $oldRules['category'];
		}
		$newRules['page'] = 0;

		$filter = $newRules;
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
		
		$items = $itemLine['items'];

		unset($itemLine['items']);

		$view = new \Phalcon\Mvc\View\Simple();

		$view->setViewsDir("../app/modules/".$this->_shop['code']."/views/");

		$result['url'] = $this->categoriesModel->buildUrl($newRules);

		$_REQUEST['_url'] = $result['url'];

		if($items < ($itemLine['limit']*$itemLine['page'])){
			$itemLine['page'] = 1;
		}

		$result['url'] = $result['url'];
		$result['html'] = $view->render("partials/catalogue/itemsline",
			array(
				'items'      => $items,
				'pagination' => $itemLine,
				'viewTranslate' => $this->_translate,
			));

		echo json_encode($result);die;
	}

	/**
	 * Генерация сайдбара
	 * @author <filchakov.denis@gmail.com>
	 * @return array
	 */
	function filterSidebar (){
		$filter = $this->categoriesModel->parseRemap($this->_shop['id'], $this->request->getQuery(), $this->_onpage);
		$sidebar = $this->categoriesModel->renderFilter($filter, $this->_shop['id']);

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
							event.preventDefault();
							$.ajax({
								type: "GET",
								url: window.location.href,
								data: "ajax=filtration&"+$(this).serialize(),
								dataType: "json",
								success: function (ajax) {
									window.history.pushState("", "", "/catalogue/"+ajax.url);
									$(".element-catalogue_items").html(ajax.html);
								}
							});
						});
					</script>';

		return array('html'=>$result);
	}

	/*
	 * Вывод ленты товаров
	 * @author <filchakov.denis@gmail.com>
	 */
	private function _lineItems($filter = array()){
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

		foreach($this->categoriesModel->getListing($this->_shop['id']) as $category){
			if(str_replace('/catalogue/','',$this->request->getQuery()['_url'])==$category['url']){
				$pagetitle = $category['name'];
			}
		}

		$title = $this->_translate['CATALOGUE'];
		$this->tag->prependTitle($title.' - ');

		// Добавляю путь в цепочку навигации
		$this->_breadcrumbs->add($pagetitle, $this->request->getURI());

		$items = $itemLine['items'];



		unset($itemLine['items']);
		$this->view->setVars([
			'template'   => 'itemsline',
			'title'	=> $pagetitle,
			'items'      => $items,
			'pagination' => $itemLine
		]);

		$this->view->pick("catalogue/index");
	}

}

