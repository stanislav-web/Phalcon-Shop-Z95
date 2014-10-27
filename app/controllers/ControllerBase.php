<?php

class ControllerBase extends Phalcon\Mvc\Controller
{
	protected

		/**
		 * Конфигурации
		 * @var null
		 */
		$_config   =   null,

		/**
		 * Язык магазина по умолчанию
		 * @var null
		 */
		$_language   =   'ru',
		/**
		 * Текущий машагин
		 * @var array
		 */
		$_shop       =   [],
		/**
		 * Срок жизни кук
		 * var int
		 */
		$_cookieSave =   604800;

	/**
	 * _getTransPath() Получаю путь у языковом файлу
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getTransPath()
	{
		$translationPath = '../app/messages/';

		$this->_language = $this->session->get("language");

		if (!$this->_language) $this->session->set("language", "ru");

		if ($language === 'en' || $language === 'ru') return $translationPath.$language;
		else  return $translationPath.'ru';


		$translationPath = '../app/messages/';

		$language = $this->session->get("language");

		if(!$language)
		{
			$this->session->set("language", $this->_language);
			if(file_exists($translationPath.$this->_language))
				return $translationPath.$this->_language;
		}
		else
		{
			$this->session->set("language", $language);
			if(file_exists($translationPath.$this->_language))
				return $translationPath.$language;
		}
	}

	/**
	 * loadMainTrans() Загружаю перевод для layout шаблонов
	 * @access public
	 * @return null
	 */
	public function loadMainTrans()
	{
		$translationPath = $this->_getTransPath();
		$messages = [];
		require $translationPath."/layout.php";

		$mainTranslate = new Phalcon\Translate\Adapter\NativeArray([
			"content" => $messages
		]);

		// $layoutTranslate - главный объект переводов в layout шаблонов
		$this->view->setVar("layoutTranslate", $mainTranslate);
	}

	/**
	 * loadCustomTrans() Загружаю перевод для конкретного контроллера
	 * @access public
	 * @return null
	 */
	public function loadCustomTrans($transFile)
	{
		$translationPath = $this->_getTransPath();
		$messages = [];
		require $translationPath.'/'.$transFile.'.php';

		//Return a translation object
		$controllerTranslate = new Phalcon\Translate\Adapter\NativeArray([
			"content" => $messages
		]);

		// $viewTranslate - главный объект переводов во views
		$this->view->setVar("viewTranslate", $controllerTranslate);
	}

	/**
	 * initialize() Инициализирую конструктор
	 * @access public
	 * @return null
	 */
	public function initialize()
	{
		$config = new \Phalcon\Config();
		$this->loadMainTrans();
	}
}
