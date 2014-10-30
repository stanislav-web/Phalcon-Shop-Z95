<?php

/**
 * Class CartController Корзина и Checkout
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
class CartController extends ControllerBase
{

	/**
	 * initialize() Инициализирую конструктор
	 * @access public
	 * @return null
	 */
	public function initialize()
	{
		// устанавливаю шаблон и загружаю локализацию
		$this->loadCustomTrans('index');
		parent::initialize();
	}

	/**
	 * indexAction() По умолчанию главная страница
	 * @access public
	 */
	public function indexAction()
	{

		// проверка страницы в кэше
		$exists = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));
		if(!$exists)
		{
			$this->view->setVar("name", "vavas");
		}
		//$this->view->cache(array("lifetime" => 1, "key" => $this->cachePage(__FUNCTION__)));
	}

}

