<?php
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
 * @var Helpers\CatalogueTags::catalogueNavTree($request) помошник для построения дерева навигации
 *
 * @package Shop
 * @subpackage Controllers
 */
class CatalogueController extends ControllerBase
{

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

			// Установка заголовка
			$this->tag->appendTitle('- '.$this->_translate['TITLE']);

			if($this->request->isGet())
			{
				$tree = Helpers\CatalogueTags::catalogueRouteTree($this->request->getURI(), [
					'categories', 'brands', 'tags'
				]);


				$this->view->setVars([

				]);
			}
		}
		// Сохраняем вывод в кэш
		if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
	}
}

