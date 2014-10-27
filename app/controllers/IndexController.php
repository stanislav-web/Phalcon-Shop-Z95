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
		$this->view->setTemplateAfter('main');
		$this->loadCustomTrans('index');
		parent::initialize();
	}

	/**
	 * indexAction() По умолчанию главная страница
	 * @access public
	 */
	public function indexAction()
	{
		// язык по умолчанию
		$language = $this->session->get('language');

		// проверка страницы в кэше
		$exists = $this->view->getCache()->exists($language.'-index');
		if(!$exists)
		{
			//@TODO передать какие нибыдь параметры в $this->viеws
			$this->view->setVar("name", "Mike");
		}
		$this->view->cache(array("lifetime" => 86400, "key" => $language.'-index'));
	}

	/**
	 * languageAction() Действие смены локализации на сайте
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

