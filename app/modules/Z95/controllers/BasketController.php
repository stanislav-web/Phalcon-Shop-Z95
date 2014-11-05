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

	public $basket = array();
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


//		$this->tag->appendTitle('- '.$this->_translate['TITLE']);
//		// есть ли в корзине вещи
//
//		if($this->session->has('cart') && $this->session->get('cart') != '') {
//			// Содержимое контроллера для формирования выдачи
//			$cart = $this->session->get('cart');
//			$ids = implode(',',array_keys($cart));
//			$products = $this->productsModel->getProductsForCart($ids, $this->_shop->price_id, $cart);
//
//			$this->view->setVar("products", $products);
//
//		}
	}

	public function updateAction()
	{
		$item = $this->request->getQuery('mode');
//		var_dump($this->request->getQuery('mode'));
//		die;
//		if($this->session->has('cart')) {
//			$session = $this->session->get('cart');
//
//			if(!empty($session) || $session != '' || null !== $session) {
//				$session[$this->request->getPost('product_id')] = $this->request->getPost();
//				$this->session->set("cart", $session);
//			} else {
//				$this->session->set("cart", array($this->request->getPost('product_id') => $this->request->getPost()));
//			}
//		} else {
//			$this->session->set("cart", array($this->request->getPost('product_id') => $this->request->getPost()));
//		}

//		$this->view->disable();

		$selected = '';
		if ($item !== false && count($item)) {

		} else {
			$this->basket['no_new_items'] = true;
//			$this->load_catalogue_items();
			$this->stash['basket'] = $this->basket;
		}
//		$this->view->disable();
		ob_start($this->view->partial('partials/basket/get'));
		ob_end_flush();

//		$view = new \Phalcon\Mvc\View();
//		$view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_NO_RENDER);
//		echo $this->view->getRender('basket', 'get');
//		print_r($this->view->getRender('basket', 'get'));
//		die;
		//Set the content of the response
		return $this->response->setContent(json_encode(
					array('success' 	=>	true,
						  'mode' 		=>	$this->request->getQuery('mode'),
						  'hash' 		=>	$this->request->getQuery('hash'),
						  'items' 		=>	$this->basket,
						  'selected'	=>	$selected,
						  'basket' 		=> 	ob_get_contents(),
				)));

	}

	public function getAction()
	{
		$this->view->disable();
//		$this->setContent("123");
//		$this->view->setVar("products", 22);
	}

	public function update() {
		$item = $this->input->get('item');
		$mode = $this->input->get('mode');

		$selected = '';

		/** Если в запросе есть позиция для обработки */
		if ($item !== false && count($item)) {
			// Пишем в статистику
			$this->load->library('stats');
			$this->stats->add('basket', key($item));

			/** Вызываем основной метод изменения состава корзины */
			$this->stash['basket'] = $this->set($item);

			/** Формируем идентификатор обработанного размера позиции для передачи js-бибиотеку */
			$id = key($item);
			if (count($item[$id]) == 1) {
				list($size, $count) = explode('_', $item[$id][0]);
				if ($count > 0) {
					$selected = $id.'_'.str_replace('?', '', str_replace('/', '_', $size));
				}
			}
		} else {
			$this->basket['no_new_items'] = true;
			$this->load_catalogue_items();
			$this->stash['basket'] = $this->basket;
		}

		/** Переменные для формирования корзины по шаблону */
		$this->stash['mode'] = $mode;
		$this->stash['categories'] = $this->catalogue_model->get_categories();
		$this->stash['shop'] = $this->structure->shop;

		/** Временный хак для сжатия результатов ajax-запросов */
		global $CFG;
		$CFG->config['compress_output'] = true;

		$result = array(
			'success' => true,
			'mode' => $mode,
			'selected' => $selected,
			'hash' => $this->input->get('hash'),
			'basket' => $this->template->get('customer/basket/get'),
			'id' => isset($id) ? $id : 0,
			'total' => isset($id) ? (isset($this->basket['items'][$id]) ? $this->basket['items'][$id]['count'] : 0) : $this->basket['total_count'],
			'items' => $this->basket['items']
		);

		// Сразу формируем краткую информацию о корзине
		// Очистим ее из сессии, если есть
		if ($this->session->userdata('basket_info') !== false) {
			$this->session->unset_userdata('basket_info');
		}

		// Генерируем по шаблону
		$this->stash['mode'] = 'info';

		$basket_info = $this->template->get('customer/basket/get');
		// Сохраняем в сессию - оттуда она берется при перезагрузках страниц
		$this->session->set_userdata('basket_info', $basket_info);

		// Добавляем в ответ сервера
		$result['basket_info'] = $basket_info;

		$this->stash['json'] = $result;
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

