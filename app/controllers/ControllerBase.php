<?php

class ControllerBase extends Phalcon\Mvc\Controller
{
	protected
		/**
		 * Язык магазины
		 * @var null
		 */
		$language   =   null,
		/**
		 * Текущий машагин
		 * @var array
		 */
		$shop       =   [],
		/**
		 * Срок жизни кук
		 * var int
		 */
		$cookieSave =   604800;

	/**
	 * _getTransPath() Получаю путь у языковом файлу
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getTransPath()
	{
		$translationPath = '../app/messages/';

		// Проверяем была ли установлен Кука ранее
		if($this->cookies->has('language'))
		{
			// Извлекаем Куку
			$lang = trim($this->cookies->get('language')->getValue());

			if(empty($lang))
			{
				$lang = $this->session->get("language");
				// Если куки выключены а кука ост.
				if(!$lang)
					$lang = $this->dispatcher->getParam("language",NULL,"ru");
			}
		}
		else
		{
			//substr("ru-RU",0, 2); / ru
			$best_lang = substr($this->request->getBestLanguage(),0,2);

			if($best_lang === "en") $lang = "en";
			else $lang = "ru";
		}

		$this->language = $lang;

		$this->cookies->set('language',$this->language, time() + 	$this->cookieSave);
		$this->session->set("language", $this->language);

		return $translationPath . $this->language;
	}

	/**
	 * loadMainTrans() Загружаю перевод для layout шаблонов
	 * @access public
	 * @return null
	 */
	public function loadMainTrans()
	{
		$translationPath = $this->_getTransPath();
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
		Phalcon\Tag::prependTitle('Prepend Title');
		$this->loadMainTrans();
	}
}
