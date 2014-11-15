<?php
	namespace Modules\Z95\Controllers;
	use Helpers\Catalogue;

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

			$this->tag->setTitle($this->_shop['title']);
		}

		/**
		 * indexAction() По умолчанию главная страница
		 * @access public
		 */
		public function indexAction()
		{
			$title = $this->tag->prependTitle($this->_translate['TITLE'].' - ');
			// Добавляю путь в цепочку навигации
			$this->_breadcrumbs->add($title, $this->request->getURI());

			// check available discounts

			$this->view->setVars(array('basket' => $this->session->get('basket'),
									   'discounts' => json_decode($this->_shop['discounts'], true)
									));



		}


		public function updateAction()
		{

			$item = $this->request->getQuery('item');
			$mode = $this->request->getQuery('mode');

			$selected = '';

			if ($item !== false && count($item)) {
				$id = key($item);
				/** Вызываем основной метод изменения состава корзины */

				if($this->session->has('basket')) {

					$this->basket = $this->session->get('basket');

					if(!empty($this->basket['items'])) {

						$basketItemIds = $this->getBasketItemsIds($this->basket['items']);

						foreach($this->basket['items'] as $key => $product) {

							if($product['product_id'] == $id) {

								list($size, $count) = explode('_', $item[$id][0]);
								if ($count > 0) {
									$this->basket['items'][$key]['sizes'] = $product['sizes'];
								} else {
									unset($this->basket['items'][$key]['sizes'][$size]);
								}
								foreach($item[$id] as $siz => $param){
									list($size, $count) = explode('_', $item[$id][$siz]);

									if($count > 0) {
										$this->basket['items'][$key]['sizes'][$size] = $count;
									}
								}

								if(!isset($this->basket['items'][$key]['sizes'])) {
									unset($this->basket['items'][$key]);
								} else if(empty($this->basket['items'][$key]['sizes'])) {
									unset($this->basket['items'][$key]);
								}


							} else if(in_array($id, $basketItemIds)){

								foreach($this->basket['items'] as $cat_id => $cat) {
									if($cat['product_id'] == $id) {
										list($size, $count) = explode('_', $item[$id][0]);
										if ($count > 0) {
											$this->basket['items'][$cat_id]['sizes'] = $cat['sizes'];
										} else {
											unset($this->basket['items'][$cat_id]['sizes'][$size]);
										}
										foreach($item[$id] as $siz => $param){
											list($size, $count) = explode('_', $item[$id][$siz]);
											if($count > 0) {
												$this->basket['items'][$cat_id]['sizes'][$size] = $count;
											}
										}

										if(!isset($this->basket['items'][$cat_id]['sizes'])) {
											unset($this->basket['items'][$cat_id]);
										} else if(empty($this->basket['items'][$cat_id]['sizes'])) {
											unset($this->basket['items'][$cat_id]);
										}
									}

								}

							} else {

								$newItems = $this->save($item);

								$this->basket['items'][] = current($this->productsModel->getBasketItems($newItems, $this->_shop['price_id']));

								break;
							}

						}
					} else {

						$newItems = $this->save($item);
						$this->basket['items'][] = current($this->productsModel->getBasketItems($newItems, $this->_shop['price_id']));
					}

				} else {

					$newItems = $this->save($item);
					$this->basket['items'][] = current($this->productsModel->getBasketItems($newItems, $this->_shop['price_id']));
				}

				/** Формируем идентификатор обработанного размера позиции для передачи js-бибиотеку */
//			$id = key($item);
				if (count($item[$id]) == 1) {
					list($size, $count) = explode('_', $item[$id][0]);
					if ($count > 0) {
						$selected = $id.'_'.str_replace('?', '', str_replace('/', '_', $size));
					}
				}

//			$basketItems = $this->productsModel->getBasketItems($this->basket['items'], $this->_shop['price_id']);

				//заполняем корзину
				$this->session->set('basket', array('items' => $this->basket['items']));

			} else {

				$this->basket['items'] = $this->session->get('basket');

				$this->basket['no_new_items'] = true;

			}

//		$this->view->disable();
			if($mode != 'small') {
				ob_start($this->view->partial('partials/basket/getBasket', array('basket' => $this->session->get('basket'),
																				 'discounts' => json_decode($this->_shop['discounts'], true)
																			)));
				ob_end_flush();
			} else {
				ob_start($this->view->partial('partials/basket/get', array('basket' => $this->session->get('basket'),
																		   'discounts' => json_decode($this->_shop['discounts'], true)
						)));
				ob_end_flush();
			}

			//@upd Stanislav WEB чтобы работал ajax в minicart
			$mini	=	Catalogue::basketMini($this->basket['items']);
			//Set the content of the response
			return $this->response->setContent(json_encode(
				array('success' 	=>	true,
					  'mode' 		=>	$this->request->getQuery('mode'),
					  'hash' 		=>	$this->request->getQuery('hash'),
					  'items' 		=>	$this->basket['items'],
					  'selected'	=>	$selected,
					  'id' 			=> 	isset($id) ? $id : 0,
					  'total'		=> 	$mini['total'],
					  'basket_info'	=>	($mini['total'] > 0) ?Catalogue::declOfNum($mini['total'], ['вещь','вещи','вещей']).' на '.$mini['sum'].' '.$this->_shop['currency_symbol']: '',
					  'basket' 		=> 	ob_get_contents(),
				)));

		}

		public function getAction()
		{
			$this->view->disable();
		}


		/**
		 * Метод сохранения корзины в сессию при отсутсвии переполнения
		 *
		 * @param array &$basket_items - новое содержимое корзины
		 *
		 * @access private
		 */
		private function save($basket_items = null) {
			/**
			 * запишим в сессию и переменную корзины ($this->basket) новый состав корзины
			 */

			if(null === $basket_items) {

				return $this->session->get('basket');
			}

			if ($this->session->has('basket') && $this->session->get('basket') != '' && null !== $this->session->get('basket')) {
				$product_id = key($basket_items);
//			if (count($basket_items[$product_id]) == 1) {
//				list($size, $count) = explode('_', $basket_items[$product_id][0]);
//			}

				$basket = $this->session->get('basket');

				//есть ли уже такой item в корзине
				if(null !== $basket['items'] && !(empty($basket['items']))) {
					$items = array();
					foreach($basket['items'] as $key => $product) {
						if($product_id == $key) {
							$items[$product_id]['sizes'] = $product['sizes'];
						}
						//добавляем к вещи только размер и кол-во

						foreach($basket_items[$product_id] as $key => $param){
							list($size, $count) = explode('_', $basket_items[$product_id][$key]);
							if($count > 0) {
								$items[$product_id]['sizes'][$size] = $count;
							}
						}

					}

				} else {
					//добавляем новую вещь

					$items = $this->productsModel->recountBasketItems($basket_items);
				}

//			$this->session->set('basket', array('items' => $basket['items']));
				return $items;

			} else {

				$items = $this->productsModel->recountBasketItems($basket_items);

				$this->session->set('basket', array('items' => $items));
				return $items;
			}
		}

		public function getBasketItemsIds($items = array())
		{
			$result = array();
			foreach($items as $item) {
				$result[] = $item['product_id'];
			}
			return $result;
		}


	}

