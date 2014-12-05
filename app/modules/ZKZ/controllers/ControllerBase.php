<?php
	namespace Modules\ZKZ\Controllers;
	use Helpers\Catalogue;

	/**
	 * Class ControllerBase Расширяет остальные контроллеры
	 *
	 * Инициализация моделей
	 *
	 * @var $this->shopModel
	 * @var $this->productsModel
	 * @var $this->categoriesModel
	 * @var $this->pricesModel
	 *
	 * Инициализация настроек, локализаций, магазина
	 *
	 * @var $this->_config      доступ ко всем настройкам
	 * @var $this->_translate   доступ к переводчику
	 * @var $this->_shop        параметры текущего магазина
	 * @var $this->_shopCategories        все категории магазина
	 *
	 * @package Shop
	 * @subpackage Controllers
	 */

	class ControllerBase extends \Phalcon\Mvc\Controller
	{
	public

		/**
		 * Определение моделей
		 * @var bool | Instance objects
		 */
		$shopModel          =   false,
		$commonModel        =   false,
		$categoriesModel    =   false,
		$brandsModel    	=   false,
		$productsModel      =   false,
		$pricesModel        =   false,
		$tagsModel			=	false,
		$bannersModel		=	false;
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
			$_lang   	    =   'ru',

			/**
			 * Объект конфигурации
			 * @var object Phalcon\Config()
			 */
			$_config        = null,

			/**
			 * Объект Кэширования из Di
			 * @var object Phalcon\DI
			 */
			$_cache        = null,

			/**
			 * Объект переводов (можно юзать в контроллерах)
			 * @var object Phalcon\Translate\Adapter\NativeArray()
			 */
			$_translate   	=   null,

			/**
			 * Текущий магазин
			 * @var object
			 */
			$_shop       	=   null,

			/**
			 * Категории текущего магазина
			 * @var null
			 */
			$_shopCategories = null,

		/**
		 * По умолчанию, хлебные крошки
		 * @var null
		 */
		$_breadcrumbs = null;

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
		$this->_translate = new \Phalcon\Translate\Adapter\NativeArray([
			"content" => $messages
		]);

		// $viewTranslate - переводы во views
		$this->view->setVar("viewTranslate", $this->_translate);
	}

	/**
	 * _getTransPath() Получаю путь у языковом файлу
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getTransPath()
	{
		$translationPath = APP_PATH.'/modules/'.$this->router->getModuleName().'/messages/';
		$language = $this->session->get("catalogue");

		if(!$language)
		{
			// устанавливаю язык по умолчанию

			$this->session->set("language", $this->_lang);

			if(file_exists($translationPath.$this->_lang))
				return $translationPath.$this->_lang;
		}
		else
		{
			if(in_array($language, array_flip($this->_languages)))
			{
				$this->_lang = $language;
				$this->session->set("language", $this->_lang);
			}
			else
			{
				$this->_lang = array_values(array_flip($this->_lang))[0];
				$this->session->set("language", $this->_lang);
			}

			if(file_exists($translationPath.$this->_lang))
				return $translationPath.$this->_lang;
		}
	}

	/**
	 * initialize() Инициализация стэша
	 * @access public
	 * @return null
	 */
	public function initialize()
	{
		// Загрузка конфигураций

		$this->_config	=	$this->di->get('config');

		// Загрузка локалей

		$this->loadMainTrans();

		// Инициализация моделей (объекты доступны в контроллерах и views)

		if(!$this->shopModel) 		$this->shopModel        =   new \Models\Shops();
		if(!$this->productsModel) 	$this->productsModel    =   new \Models\Products();
		if(!$this->categoriesModel) $this->categoriesModel  =   new \Models\Categories();
		if(!$this->brandsModel) 	$this->brandsModel  	=   new \Models\Brands();
		if(!$this->pricesModel) 	$this->pricesModel      =   new \Models\Prices();
		if(!$this->tagsModel) 		$this->tagsModel      	=   new \Models\Tags();
		if(!$this->bannersModel) 	$this->bannersModel     =   new \Models\Banners();

		// Получение параметров текущего магазина

		if(null === $this->_shop)
			$this->_shop = $this->shopModel->get(['code'	=>	$this->router->getModuleName()],[], 1, true);

		// Получение категорий и подкатегорий для текущего магазина
		$this->_shopCategories = $this->categoriesModel->getShopCategories($this->_shop['id'], true);

		// Инициализация навигации

		$nav = $this->di->get('navigation');
		$this->_breadcrumbs = $this->di->get('breadcrumbs');

		// проверка корзины
		$minicart = $this->session->get('cart');

		//$this->session->remove('cart');

		// проверка refer
		if(!$this->session->has('ref'))
			$this->session->set('ref', $this->request->getQuery('ref', null, ''));
		if(!$this->session->has('refer_data'))
			$this->session->set('refer_data', json_encode($this->request->getQuery('refer_data', null, '')));

		// В конце запись переменных для шаблонов
		$this->session->set('price_id', $this->_shop['price_id']);

		$this->view->setVars([
			'minicart'		=>	(isset($minicart['meta']) && $minicart['meta']['total'] > 0) ? $minicart : [],		// информация по мини корзине
			'language'	    =>	$this->_lang,       // текущий язык
			'languages'	    =>	$this->_languages,  // все доступные языки
			'shop' 		    => 	$this->_shop,       // параметры магазина
			'navigation' 	=> 	$nav,               // топ меню навигации
		]);
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

		$mainTranslate = new \Phalcon\Translate\Adapter\NativeArray([
			"content" => $messages
		]);

		// $layoutTranslate переводы в layout шаблонов
		$this->view->setVar("layoutTranslate", $mainTranslate);
	}

	/**
	 * Событие после выполнения всего роута
	 * Срабатывает, когда фреймворку известен только роутинг
	 * @param \Phalcon\Mvc\Dispatcher $dispatcher
	 * @return null
	 */
	public function afterExecuteRoute(\Phalcon\Mvc\Dispatcher $dispatcher)
	{
		// Ставлю подхват хлебных крошек, когда уже произошел роутинг в контроллерах и добавлен путь в ->add()
		$this->view->setVar('breadcrumbs', $this->_breadcrumbs->generate()); // крошки
	}

	/**
	 * Генерация ключа для кэширование views
	 * @param $method Метод action из контроллера
	 * @return string
	 */
	public function cachePage($method)
	{
		if($this->_shop)
		{
			$md5 = strtolower($this->_shop->code.'-'.$this->_lang.'-'.($this->request->getURI() == '/') ? '_' : $this->request->getURI().'-'.substr($method, 0, -6));
			return $md5.'.cache';
		}
	}

	/**
	 * setJsonResponse() Установка режима выдачи ответа в JSON
	 * @access protected
	 * @return null
	 */
	protected  function setJsonResponse()
	{
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
	}
}
