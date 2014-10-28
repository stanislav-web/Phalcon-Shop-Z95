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

