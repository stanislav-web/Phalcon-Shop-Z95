<?php

	class ControllerBase extends Phalcon\Mvc\Controller
	{
		protected

			/**
			 * Допустимые для магазина языки
			 * @var null
			 */
			$_languages   	=   ['ru' => 'Русский', 'en' => 'English'],

			/**
			 * Текущая локаль
			 * @var null
			 */
			$_language   	=   null,

			/**
			 * Объект конфигурации
			 * @var object Phalcon\Config()
			 */
			$_config = null,

			/**
			 * Обьъект переводов (можно юзать в контроллерах)
			 * @var object Phalcon\Translate\Adapter\NativeArray
			 */
			$_translate   	=   null,

			/**
			 * Текущий магазин
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
				$this->_language = array_values(array_flip($this->_languages))[0];

				$this->session->set("language", $this->_language);

				if(file_exists($translationPath.$this->_language))
					return $translationPath.$this->_language;
			}
			else
			{
				if(in_array($language, array_flip($this->_languages)))
				{
					$this->_language = $language;
					$this->session->set("language", $this->_language);

				}
				else
				{
					$this->_language = array_values(array_flip($this->_languages))[0];
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
			$this->_translate = new Phalcon\Translate\Adapter\NativeArray([
				"content" => $messages
			]);

			// $viewTranslate - главный объект переводов во views
			$this->view->setVar("viewTranslate", $this->_translate);
		}

		/**
		 * initialize() Инициализирую конструктор
		 * @access public
		 * @return null
		 */
		public function initialize()
		{
			// Загрузка конфигураций

			$this->_config	=	$this->di->get('config');

			// Загрузка локалей

			$this->loadMainTrans();

			// Инициализация магазина

			$shop = new Models\Shops();
			$this->_shop = $shop->get(['host'	=>	$this->request->getHttpHost()],[], 1);

			$sqlCategories = "SELECT ".Models\Categories::TABLE.".*
				FROM ".Models\Categories::TABLE." WHERE ".Models\Categories::TABLE.".parent_id = 0";
			
			$categories = (object)$this->db->query($sqlCategories)->fetchAll();

			$sqlNewProducts = "SELECT ".Models\Products::TABLE.".*, ".Models\Prices::TABLE.".price
				 FROM ".Models\Products::TABLE."
				 INNER JOIN ".Models\Prices::TABLE." ON ".Models\Products::TABLE.".id = ".Models\Prices::TABLE.".product_id
				 WHERE ".Models\Products::TABLE.".published = 1
				 AND ".Models\Prices::TABLE.".id = " . $this->_shop->id .
				 " ORDER BY ".Models\Products::TABLE.".date_create DESC LIMIT 6";

			$newProducts = $this->db->query($sqlNewProducts)->fetchAll();
			$this->view->setVars(array('shop' => $shop,
									   'categories' => $categories,
									   'newProducts' => $newProducts
								));
			// Установка директории с шаблонами
			$this->view->setViewsDir($this->_config->application->viewsDir.'/'.$this->_shop->code);

			// Инициализация навигации

			$nav = $this->di->get('navigation');

			$nav->setActiveNode(
				$this->router->getActionName(),
				$this->router->getControllerName()
			);

			$this->view->setVars([
				'language'	=>	$this->_language,
				'languages'	=>	$this->_languages,
				'shop' 		=> 	$this->_shop,
				'topnav' 	=> 	$nav,
			]);
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
