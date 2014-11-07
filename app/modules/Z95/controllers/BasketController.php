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

		$this->tag->setTitle($this->_shop['title']);
	}

	/**
	 * indexAction() По умолчанию главная страница
	 * @access public
	 */
	public function indexAction()
	{


		$this->view->setVar('basket', $this->session->get('basket'));
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

		$item = $this->request->getQuery('item');
		$mode = $this->request->getQuery('mode');

		$selected = '';

//		$this->session->destroy();die;
		if ($item !== false && count($item)) {
			$id = key($item);
			/** Вызываем основной метод изменения состава корзины */
//			$this->basket['items'] = $this->save($item);
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
			ob_start($this->view->partial('partials/basket/getBasket', array('basket' => $this->session->get('basket'))));
			ob_end_flush();
		} else {
			ob_start($this->view->partial('partials/basket/get', array('basket' => $this->session->get('basket'))));
			ob_end_flush();
		}



		//Set the content of the response
		return $this->response->setContent(json_encode(
				array('success' 	=>	true,
					  'mode' 		=>	$this->request->getQuery('mode'),
					  'hash' 		=>	$this->request->getQuery('hash'),
					  'items' 		=>	$this->basket['items'],
					  'selected'	=>	$selected,
					  'id' 			=> 	isset($id) ? $id : 0,
					  'total'		=> 	0,
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

	/**
	 * Метод загрузки информации о позициях в корзине из каталога
	 *
	 * @access private
	 */
	private function load_catalogue_items() {
		/** Берем id всех позиций в корзине */
		$ids = array_keys($this->basket['items']);

		/** Для выбранных позиций загружаем информацию о них из каталога */
		//$this->basket['catalogue_items'] = !empty($ids) ? $this->catalogue_model->get_items_by_array($ids) : array();
		$this->basket['catalogue_items'] = !empty($ids) ? $this->getItemsByArray($ids) : array();
	}

	/**
	 * Основной метод изменения состава корзины
	 *
	 * @param array		$item	Массив обрабатываемых позиций
	 *
	 * @access private
	 */
	private function set($item) {
		/** Выделяем идентификаторы обрабатываемых позиций. Сейчас используется только одна позиция за один вызов этой функции */
		$ids = array_keys($item);

		/** Запомним только что обработанную позицию */
		$this->basket['last_item'] = count($ids) ? $ids[0] : 0;

		/** Поучаем информацию из каталога по обрабатываемым позициям */
		$items = $this->ElasticCatalogueModel->getItemsByArray($ids);
		//$items = $this->catalogue_model->get_items_by_array($ids);

		/** Копируем начальное содержимое корзины */
		$basket_items = $this->basket['items'];

		foreach ($item as $id => $sizes) {
			if (!isset($basket_items[$id])) {
				/** Если обрабатываемой позиции не было в корзине, то добавляем запись для нее */
				$basket_items[$id] = array('sizes' => array());
			}

			/** Устанавливаем ее цену из каталога */
			$basket_items[$id]['price'] = $items[$id]['discount_price'];

			/** Приступаем к обработке размеров для этой позиции */
			foreach($sizes as $size => $size_count) {
				/** Выделяем из полученной информации отдельно размер и количество */
				list($size, $count) = explode('_', $size_count);

				/** Устанавливем в корзине новое количество для данного размера */
				if (strpos($count, '+') !== false && isset($basket_items[$id]['sizes'][$size])) {
					/** Если количество начинается с "+", то увеличивем начальное количество на данное число */
					$basket_items[$id]['sizes'][$size] += intval($count);
				} else {
					$basket_items[$id]['sizes'][$size] = intval($count);
				}

				// Если количество вещей данного размера <= 0, то удалить этот размер из позиции
				if ($basket_items[$id]['sizes'][$size] <= 0) {
					unset($basket_items[$id]['sizes'][$size]);
				}

				// Если у позиции не осталось размеров, то удалить из корзины
				if (empty($basket_items[$id]['sizes'])) {
					unset($basket_items[$id]);
				}
			}

			/** Если позиция все еще в корзине, то проверим доступность запрошенного количества каждого размера */
			if (isset($basket_items[$id])) {
				if ($items[$id]['cat_type'] != 0) {
					$basket_items[$id]['no_discount'] = 1;
				}

				/** Получаем массив доступных количеств всех размеров данной позиции */
				$available_sizes = $this->catalogue_model->parse_sizes($items[$id]['cat_sizes_count']);

				/** Прикрепим к корзине информацию о недобавленных размерах */
				$this->check_declined_sizes($basket_items, $id, $sizes, $available_sizes);
			}
		}

		$this->save($basket_items);

		return $this->basket;
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

	/**
	 * Метод расчета параметров корзины - суммы, скидки, количества позиций
	 *
	 * @access private
	 */
	private function recount() {
		/** Обновим массив с информаций из каталога по позициям в корзине */
		//$this->load_catalogue_items();

		/**
		 * Если надо всегда брать текущие цены, то каждый раз берем их из каталога, а не из корзины
		 *
		 * $this->set_catalogue_prices();
		 */

		$basket = $this->basket; 				// Просто сокращение
		$basket['total_count'] = 0;				// Всего позиций
		$basket['total_count_for_discount'] = 0;// Всего позиций для расчета скидки
		$basket['total_price'] = 0;				// Общая стоимость товаров в корзине
		$basket['total_price_for_discount'] = 0;// Общая стоимость товаров в корзин для расчета скидки
		$basket['discount'] = 0;				// Величина скидки (%)
		$basket['discount_type'] = '';			// Тип скидки (COUNT | SUM)
		$basket['discounts'] = array();			// Массив уровней разных % значений скидки
		$basket['discount_diff'] = 0;			// Сколько осталось до следующего уровня скидки
		$basket['discount_next'] = 0;			// Величина скидки (%) на следующем уровне
		$basket['discount_amount'] = 0;			// Величина скидки в деньгах
		$basket['total_discount_price'] = 0;	// Общая стоимость товаров в корзине с учетом скидки

		/** Выпоняем расчеты вышеназванных величин */
		$this->calc_counts();
//		$this->calc_discounts();
	}


	/**
	 * Метод расчета количества товаров в корзине и их общей стоимости
	 *
	 * @access private
	 */
	private function calc_counts() {
		$basket = $this->basket;

		/** Проходим по всем размерам всех позиций в корзине */
		if (count($basket['items'])) {
			foreach($basket['items'] as $id => $item) {

				/** Здесь будет количество вещей всех размеров одной позиции */
				$basket['items'][$id]['count'] = 0;

				foreach($item['sizes'] as $size => $count) {
					if ($size == '?' and count($item['sizes']) > 1) {
						/**
						 * Если у этой позиции есть не только неопределенный размер,
						 * то не учитываем отдельно неопределенный размер
						 */
					} else {
						$basket['items'][$id]['count'] += $count;
						$basket['total_count'] += $count;
						$basket['total_price'] += $item['price']*$count;

						if (empty($basket['items'][$id]['no_discount'])) {
							$this->load->model('CatalogueModel');
							$ee = $this->CatalogueModel->getItemsByIdsItems(array($id));
							if($ee[0]['cat_type']==0){
								$basket['total_count_for_discount'] += $count;
								$basket['total_price_for_discount'] += $item['price']*$count;
							} else {
								$basket['discount_perfume'] = lang('Скидка на парфюмерию не распространяется!');
							}
						}
					}
				}
			}
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

