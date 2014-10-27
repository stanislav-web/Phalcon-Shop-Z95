<?php
	class ErrorController extends ControllerBase
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
		public function show404Action()
		{
			Phalcon\Tag::setTitle('404 Not Found');
			$this->view->setTemplateAfter('main');
			$this->response->setStatusCode(404, 'Not Found');
		}
	}