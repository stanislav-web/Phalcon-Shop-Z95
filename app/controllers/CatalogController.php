<?php
class CatalogController extends ControllerBase
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
}

