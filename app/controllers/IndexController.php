<?php

/**
 * Class IndexController Главная страница и статика
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
class IndexController extends ControllerBase
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

		// Заголовок страницы
		$this->tag->setTitle($this->_shop->title);
	}

	/**
	 * indexAction() По умолчанию главная страница
	 * @access public
	 */
	public function indexAction()
	{
		// проверка страницы в кэше

		$content = null;
		if($this->_config->cache->frontend)
		{
			$content = $this->_cache->start($this->cachePage(__FUNCTION__));
		}

		if($content === null)
		{
			// Содержимое контроллера для формирования выдачи

			$modelProducts = new \Models\Products();
			$newProducts = $modelProducts->get([], ['id' => 'DESC'], 10);
			$this->view->setVar("newProducts", $newProducts);

			$topProducts = $modelProducts->get([], ['rating' => 'DESC'], 10);
			$this->view->setVar("topProducts", $topProducts);

			// Сохраняем вывод в кэш
			$this->_cache->save();

		}
		else return $content;
	}

	/**
	 * aboutAction() Страница "О НАС"
	 * @access public
	 */
	public function aboutAction()
	{
		// проверка страницы в кэше

		$content = null;
		if($this->_config->cache->frontend)
		{
			$content = $this->_cache->start($this->cachePage(__FUNCTION__));
		}

		if($content === null)
		{
			// Содержимое контроллера для формирования выдачи



			// Сохраняем вывод в кэш
			$this->_cache->save();

		}
	}

	/**
	 * communityAction() Страница "СООБЩЕСТВО"
	 * @access public
	 */
	public function communityAction()
	{
		// проверка страницы в кэше

		$content = null;
		if($this->_config->cache->frontend)
		{
			$content = $this->_cache->start($this->cachePage(__FUNCTION__));
		}

		if($content === null)
		{
			// Содержимое контроллера для формирования выдачи



			// Сохраняем вывод в кэш
			$this->_cache->save();

		}
	}

	/**
	 * deliveryAction() Страница "ДОСТАВКА"
	 * @access public
	 */
	public function deliveryAction()
	{
		// проверка страницы в кэше

		$content = null;
		if($this->_config->cache->frontend)
		{
			$content = $this->_cache->start($this->cachePage(__FUNCTION__));
		}

		if($content === null)
		{
			// Содержимое контроллера для формирования выдачи



			// Сохраняем вывод в кэш
			$this->_cache->save();

		}
	}


	/**
	 * languageAction() Смена локализации на сайте
	 * @access public
	 */
	public function languageAction($language = '')
	{
		// Смена языка и перезагрузка файла локализации

		$this->session->set('language', $language);
		$this->loadMainTrans();
		$this->loadCustomTrans('index');

		// Ставлю переадресацию обратно откуда зашел

		$referer = $this->request->getHTTPReferer();

		if(strpos($referer, $this->request->getHttpHost()."/")!==false)
			return $this->response->setHeader("Location", $referer);
		else
			return $this->dispatcher->forward(['controller' => 'index', 'action' => 'index']);
	}
}

