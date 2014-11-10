<?php
namespace Modules\Z95\Controllers;

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
 * @var $this->_helper      помошник для каталога
 *
 * @package Shop
 * @subpackage Controllers
 */

class ControllerBase extends \Phalcon\Mvc\Controller
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
		$_lang   	    =   'ru',

		/**
		 * Объект конфигурации
		 * @var object Phalcon\Config()
		 */
		$_config        = null,

		/**
		 * Объект помощника
		 * @var object Helpers\CatalogueTags()
		 */
		$_helper        = null,

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
		$_breadcrumbs = null,

		/**
		 * скидки
		 * @var null
		 */
		$_discounts = null;

	public

		/**
		 * Определение моделей
		 * @var bool | Instance objects
		 */
		$shopModel          =   false,
		$commonModel        =   false,
		$categoriesModel    =   false,
		$productsModel      =   false,
		$pricesModel        =   false,
		$tagsModel			=	false,
		$bannersModel		=	false;

	/**
	 * _getTransPath() Получаю путь у языковом файлу
	 *
	 * @access protected
	 * @return array
	 */
	protected function _getTransPath()
	{
		$translationPath = APP_PATH.'/modules/'.$this->router->getModuleName().'/messages/';

		$language = $this->session->get("language");

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
	 * initialize() Инициализация стэша
	 * @access public
	 * @return null
	 */
	public function initialize()
	{
		// Загрузка конфигураций

		$this->_config	=	$this->di->get('config');
		$this->_helper	=	new \Helpers\CatalogueTags();

		// Загрузка локалей и навигации

		$this->_breadcrumbs = $this->di->get('breadcrumbs');

		$this->loadMainTrans();

		// Инициализация моделей (объекты доступны в контроллерах и views)

		$this->commonModel      =   new \Models\Common();
		$this->shopModel        =   new \Models\Shops();
		$this->productsModel    =   new \Models\Products();
		$this->categoriesModel  =   new \Models\Categories();
		$this->pricesModel      =   new \Models\Prices();
		$this->tagsModel      	=   new \Models\Tags();
		$this->bannersModel     =   new \Models\Banners();

		// Получение параметров текущего магазина

		$this->_shop = $this->shopModel->get(['code'	=>	$this->router->getModuleName()],[], 1, true);

		// Получение категорий и подкатегорий для текущего магазина
		$this->_shopCategories = $this->commonModel->getShopCategories($this->_shop['id'], true);
		
		// Инициализация навигации

		$nav = $this->di->get('navigation');

		// получение скидок магазина
		$this->_discounts = $this->shopModel->checkDiscounts($this->_shop);

		// В конце запись переменных для шаблонов
		$this->view->setVars([
			'language'	    =>	$this->_lang,       // текущий язык
			'languages'	    =>	$this->_languages,  // все доступные языки
			'shop' 		    => 	$this->_shop,       // параметры магазина
			'navigation' 	=> 	$nav,               // топ меню навигации
			'newProducts'   =>  $this->productsModel->getNewProducts($this->_shop['price_id'], 6,  true)       // новые товары
		]);
	}

	/**
	 * Событие после выполнения всего роута
	 */
	public function afterExecuteRoute()
	{
		// Ставлю подхват хлебных крошек, когда уже произошел роутинг в контроллерах и добавлен путь в ->add()
		$this->view->setVar('breadcrumbs', $this->_breadcrumbs->generate()); // крошки
	}

	/**
	 * Страница для кэша
	 * @param $method Метод action из контроллера
	 * @return string
	 */
	public function cachePage($method)
	{
		if($this->_shop)
			return strtolower($this->_shop->code.'-'.$this->_lang.'-'.($this->request->getURI() == '/') ? '_' : $this->request->getURI().'-'.substr($method, 0, -6).'.cache');
	}
}
