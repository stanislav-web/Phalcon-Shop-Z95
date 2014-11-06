<?php
	namespace Modules\Z95\Controllers;
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
		 * initialize() Инициализирую конструктор
		 * @access public
		 * @return null
		 */
		public function initialize()
		{
			// устанавливаю шаблон и загружаю локализацию
			$this->loadCustomTrans('index');
			parent::initialize();

			// Заголовок страницы
			$this->tag->setTitle($this->_shop['title']);
		}

		/**
		 * indexAction() По умолчанию главная страница
		 * @access public
		 */
		public function indexAction()
		{
			// проверка страницы в кэше

			$content = null;
			if($this->_config->cache->frontend)
			{
				$this->view->cache(array(
					"service" => "viewCache",
					"key" 		=> $this->cachePage(__FUNCTION__)
				));

				$content = $this->view->getCache()->exists($this->cachePage(__FUNCTION__));
			}

			if($content === null)
			{
				// Содержимое контроллера для формирования выдачи

				// Формирую заголовок

				$title = $this->_shop['title'];

				// получение баннеров
				$banners = $this->bannersModel->getBanners($this->_shop['id'], true);

				// получаю все дочерние категории каталога
				// Получение подкатегорий выбранного магазина с изображением самого рейтингового товара в каждой категории

				$subCategories = $this->categoriesModel->getSubcategories($this->_shop['id'], 0, '>', 'DESC', true);

				// вывожу по умолчанию страницу каталога c вложением subcategories
				$this->view->setVars([
					'banners'			=>	$banners,
					'subcategories'		=>	$subCategories,
					'title'				=>	$title,
				]);
			}
			// Сохраняем вывод в кэш
			if($this->_config->cache->frontend) $this->view->cache(array("key" => $this->cachePage(__FUNCTION__)));
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

		public function aboutAction()
		{

		}

		public function discountsAction()
		{

		}

		public function deliveryAction()
		{

		}

		public function returnAction()
		{

		}

		public function optAction()
		{

		}
	}

