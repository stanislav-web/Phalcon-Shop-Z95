<?php
/**
 * Class CatalogController Каталог (Карточка товара, вывод из категорий)
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

	/**
	 * initialize() Инициализирую конструктор
	 * @access public
	 * @return null
	 */
	public function initialize()
	{
		// устанавливаю шаблон и загружаю локализацию
		$this->loadCustomTrans('catalog');
		parent::initialize();

		// Заголовок страницы
		$this->tag->setTitle($this->_shop->title);

	}

	/**
	 * indexAction() По умолчанию главная страница
	 * @access public
	 */
	public function indexAction()
	{

		$this->tag->appendTitle('- '.$this->_translate['TITLE']);

		// Подсчет всех опубликованных товаров
		$psql = "SELECT COUNT(1) AS products_count
				FROM ".Products::TABLE." WHERE ".Products::TABLE.".published = 1";
		$products = (object)$this->db->query($psql)->fetch();

		$this->view->setVars(array(
			'products_count'	=>	$products->products_count
		));
	}

	public function itemAction()
	{

		var_dump($this->request->getURI());
		die;

	}
}

