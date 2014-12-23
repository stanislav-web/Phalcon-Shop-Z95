<?php
	namespace Modules\ZKZ\Controllers;
	use \Helpers\Catalogue;
    use Phalcon\Exception;

    /**
	 * Class IndexController Главная страница и статика
	 *
	 * Доступ к моделям
	 *
	 * @var $this->shopModel
	 * @var $this->productsModel
	 * @var $this->categoriesModel
	 * @var $this->pricesModel
	 *
	 * @var $this->_config      доступ ко всем настройкам
	 * @var $this->_translate   доступ к переводчику
	 * @var $this->_shop        параметры текущего магазина
	 *
	 * @var $this->di           вызов компонентов из app/config/di.php
	 * @var $this->session      вызов сессии
	 * @var $this->request      информация об HTTP запросах
	 * @var $this->router       посмотреть параметры текущего роута, настроить роуты
	 *
	 * @package Shop
	 * @subpackage Controllers
	 */
	class IndexController extends ControllerBase
	{

		/**
		 * Баннера
		 * @var bool
		 */
		private $banners	=	false;

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

			// Получаю баннер для страницы
			$this->banners = $this->bannersModel->getBanners($this->_shop['id'], true);

			// Заголовок страницы
			$this->tag->setTitle($this->_shop['title']);
		}

		/**
		 * indexAction() По умолчанию главная страница
		 * @access public
		 */
		public function indexAction()
		{
			// Формирую заголовок

			$title = $this->_shop['title'];

			// Получение подкатегорий выбранного магазина с изображением самого рейтингового товара в каждой категории

			$subCategories = $this->categoriesModel->getCategories($this->_shop['id'], '', 'ASC', true);

			// вывожу по умолчанию страницу каталога c вложением subcategories
			$this->view->setVars([
				'banners'			=>	$this->banners,
				'tree'				=>	Catalogue::categoriesToTree($this->_shopCategories, 0, true),
                'subcategories'		=>	Catalogue::arrayToAssoc($subCategories, 'id'),
                'count_products'	=>	Catalogue::arrayToAssoc($this->_countProducts, 'category_id'),
				'title'				=>	$title,
			]);

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

			if(strpos($referer, $this->request->getHttpHost()."/")!==false)
				return $this->response->setHeader("Location", $referer);
			else
				return $this->dispatcher->forward(['controller' => 'index', 'action' => 'index']);
	}

	/**
	 * Экшн для вывода статичестких страниц
	 * @param $page ?page=/about/tratata => /about/tratata
	 */
	public function staticAction($page)
	{
		// Устанавливаю заголовок
		$title = $this->_translate[strtoupper($page)];
		$this->tag->prependTitle($title.' - ');

		// навигация
		$this->_breadcrumbs->unsetLast();

		if($page != 'about')
			$this->_breadcrumbs->add($this->_translate['ABOUT'], 'about/')
								->add($title, 'about/');
		else	$this->_breadcrumbs->add($title);

		$this->view->setVars([
			'banners'			=>	$this->banners,
			'title'				=>	$title,
			'template'			=>	strtolower($page),
		]);

		return $this->view->pick("static/index");
	}
}

