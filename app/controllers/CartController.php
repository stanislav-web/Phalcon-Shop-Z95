<?php

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

