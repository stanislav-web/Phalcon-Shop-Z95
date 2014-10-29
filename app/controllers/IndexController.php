<?php

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
			//@TODO передать какие нибыдь параметры в $this->viеws
			$this->view->setVar("name", "Mike");
		}
		$modelProducts = new \Models\Products();
		$newProducts = $modelProducts->get(array(), array('id' => 'DESC'), 2);
		$this->view->setVar("newProducts", $newProducts);

		$topProducts = $modelProducts->get(array(), array('rating' => 'DESC'), 2);
		$this->view->setVar("topProducts", $topProducts);

		$featuredProducts = $modelProducts->get(array(), array('date_create' => 'DESC'), 2);
		$this->view->setVar('featuredProducts', $featuredProducts);

		//$this->view->cache(array("lifetime" => 1, "key" => $this->cachePage(__FUNCTION__)));
	}

	/**
	 * aboutAction() Страница "О НАС"
	 * @access public
	 */
	public function aboutAction()
	{
		// проверка страницы в кэше
		$exists = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));

		if(!$exists)
		{
			//@TODO передать какие нибыдь параметры в $this->viеws
			$this->view->setVar("name", "Mike");
		}
		
		//$this->view->cache(array("lifetime" => 1, "key" => $this->cachePage(__FUNCTION__)));
	}

	/**
	 * communityAction() Страница "СООБЩЕСТВО"
	 * @access public
	 */
	public function communityAction()
	{
		// проверка страницы в кэше
		$exists = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));

		if(!$exists)
		{
			//@TODO передать какие нибыдь параметры в $this->viеws
		}
		//$this->view->cache(array("lifetime" => 1, "key" => $this->cachePage(__FUNCTION__)));
	}

	/**
	 * deliveryAction() Страница "ДОСТАВКА"
	 * @access public
	 */
	public function deliveryAction()
	{
		// проверка страницы в кэше
		$exists = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));

		if(!$exists)
		{
			//@TODO передать какие нибыдь параметры в $this->viеws
			$this->view->setVar("name", "Mike");
		}
		//$this->view->cache(array("lifetime" => 1, "key" => $this->cachePage(__FUNCTION__)));
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
		if(strpos($referer, $this->request->getHttpHost()."/")!==false) return $this->response->setHeader("Location", $referer);
		else
			return $this->dispatcher->forward(['controller' => 'index', 'action' => 'index']);
	}
}

