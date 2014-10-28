<?php

	class ControllerBase extends Phalcon\Mvc\Controller
	{
		protected

			/**
			 * Допустимые для магазина языки
			 * @var null
			 */
			$_languages   	=   ['ru', 'en'],

			/**
			 * Текущая локаль
			 * @var null
			 */
			$_language   	=   null,

			/**
			 * Текущий машагин
			 * @var array
			 */
			$_shop       	=   [],
			/**
			 * Срок жизни кук
			 * var int
			 */
			$_cookieSave 	=   604800;

		/**
		 * _getTransPath() Получаю путь у языковом файлу
		 *
		 * @access protected
		 * @return array
		 */
		protected function _getTransPath()
		{
			$translationPath = '../app/messages/';

			$language = $this->session->get("language");

			if(!$language)
			{
				// устанавливаю язык по умолчанию
				$this->_language = array_values($this->_languages)[0];

				$this->session->set("language", $this->_language);

				if(file_exists($translationPath.$this->_language))
					return $translationPath.$this->_language;
			}
			else
			{
				if(in_array($language, $this->_languages))
				{
					$this->_language = $language;
					$this->session->set("language", $this->_language);

				}
				else
				{
					$this->_language = array_values($this->_languages)[0];
					$this->session->set("language", $this->_language);
				}

				if(file_exists($translationPath.$this->_language))
					return $translationPath.$this->_language;
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
			// Загрузка локалей

			$this->loadMainTrans();

			// Инициализация магазина

			$shop = Shops::findFirst(array(
				"host = '{$this->request->getHttpHost()}'",
				"limit" => 1
			));
			$this->_shop = $shop;

			// Глобальная видимость во всех шаблонах $this->shop->....
			$this->view->setVar('shop', $shop);

			// Установка директории с шаблонами
			$this->view->setViewsDir($this->di->get('config')->application->viewsDir.'/'.$this->_shop->code);
		}

		/**
		 * Страница для кэша
		 * @param $method
		 * @return string
		 */
		public function cachePage($method)
		{
			if($this->_shop)
				return strtolower($this->_shop->code.'-'.$this->_language.'-'.substr($method, 0, -6));
		}
	}
