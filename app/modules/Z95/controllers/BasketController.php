<?php
namespace Modules\Z95\Controllers;

/**
 * Class BasketController Корзина
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
 * @package Basket
 * @subpackage Controllers
 */
class BasketController extends ControllerBase
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

		$this->tag->setTitle($this->_shop->title);
	}

	/**
	 * indexAction() По умолчанию главная страница
	 * @access public
	 */
	public function indexAction()
	{

		var_dump('index');
		die;
		$this->tag->appendTitle('- '.$this->_translate['TITLE']);
		// есть ли в корзине вещи

		if($this->session->has('cart') && $this->session->get('cart') != '') {
			// Содержимое контроллера для формирования выдачи
			$cart = $this->session->get('cart');
			$ids = implode(',',array_keys($cart));
			$products = $this->productsModel->getProductsForCart($ids, $this->_shop->price_id, $cart);

			$this->view->setVar("products", $products);

		}
	}

	public function addToCartAction()
	{
		var_dump('add-to-basket');
		die;
		if($this->session->has('cart')) {
			$session = $this->session->get('cart');

			if(!empty($session) || $session != '' || null !== $session) {
				$session[$this->request->getPost('product_id')] = $this->request->getPost();
				$this->session->set("cart", $session);
			} else {
				$this->session->set("cart", array($this->request->getPost('product_id') => $this->request->getPost()));
			}
		} else {
			$this->session->set("cart", array($this->request->getPost('product_id') => $this->request->getPost()));
		}

		$this->view->disable();

		//Set the content of the response
		return $this->response->setContent(json_encode(array('result' => true)));

	}

	public function removeFromCartAction()
	{
		var_dump('add-remove-from-basket');
		die;
		if($this->session->has('cart') && $this->session->get('cart') != '') {

			$session = $this->session->get('cart');
			$this->view->disable();
			unset($session[$this->request->getPost('product_id')]);
			$this->session->set("cart", $session);
			return $this->response->setContent(json_encode(array('result' => true)));
		}
	}

}

