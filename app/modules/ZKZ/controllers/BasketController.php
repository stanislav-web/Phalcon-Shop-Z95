<?php
	namespace Modules\ZKZ\Controllers;
	use
		\Phalcon\Mvc\View,
		Helpers\Catalogue;

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
	 *
	 * @package Basket
	 * @subpackage Controllers
	 */
	class BasketController extends ControllerBase
	{

		public $basket = [];
		/**
		 * initialize() Инициализирую конструктор
		 * @access public
		 * @return null
		 */
		public function initialize()
		{
			// устанавливаю шаблон и загружаю локализацию
			$this->loadCustomTrans('basket');
			parent::initialize();

			$this->tag->setTitle($this->_shop['title']);
		}

		/**
		 * indexAction() По умолчанию главная страница
		 * @access public
		 */
		public function indexAction()
		{
			$title = $this->_translate['TITLE'];

			$this->tag->prependTitle($title.' - ');

			// Добавляю путь в цепочку навигации

			// корректирую мета данные
			$this->_breadcrumbs->reset();
			$this->_breadcrumbs
				->add($this->_translate['MAIN'], '/')
				->add($title, $this->request->getURI());

			$basket  = ($this->session->get('basket')) ? $this->session->get('basket') : [];

			// передаю подсчитанные товары + скидки магазина в view
			if(!empty($basket['items']))
				$items = Catalogue::recountBasket($basket, $this->_shop['discounts']);
			else $items	=	$basket;

			$this->session->set('informer', $items['informer']);
			$this->view->setVars([
					'title'		=>	$title,
					'basket' 	=> 	(isset($items)) ? $items : [],
			]);
		}

		/**
		 * updateAction() Обновление , добавление в корзину
		 * @return \Phalcon\Http\ResponseInterface
		 */
		public function updateAction()
		{

			$item = $this->request->getQuery('item');
			$mode = $this->request->getQuery('mode');

			$selected = '';

			if($item !== false && count($item))
			{
				$id = key($item);

				if($this->session->has('basket'))
				{
					$this->basket = $this->session->get('basket');

					if(!empty($this->basket['items']))
					{
						$basketItemIds = $this->getBasketItemsIds($this->basket['items']);

						foreach($this->basket['items'] as $key => $product)
						{
							if($product['product_id'] == $id)
							{
								list($size, $count) = explode('_', $item[$id][0]);

								if ($count > 0)
									$this->basket['items'][$key]['sizes'] = $product['sizes'];
								else
									unset($this->basket['items'][$key]['sizes'][$size]);

								foreach($item[$id] as $siz => $param)
								{
									list($size, $count) = explode('_', $item[$id][$siz]);

									if($count > 0)
										$this->basket['items'][$key]['sizes'][$size] = $count;
								}

								if(!isset($this->basket['items'][$key]['sizes']))
									unset($this->basket['items'][$key]);
								else if(empty($this->basket['items'][$key]['sizes']))
									unset($this->basket['items'][$key]);

							}
							else if(in_array($id, $basketItemIds))
							{

								foreach($this->basket['items'] as $cat_id => $cat) {
									if($cat['product_id'] == $id) {
										list($size, $count) = explode('_', $item[$id][0]);
										if ($count > 0) {
											$this->basket['items'][$cat_id]['sizes'] = $cat['sizes'];
										} else
											unset($this->basket['items'][$cat_id]['sizes'][$size]);

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

							}
							else
							{
								$newItems = $this->save($item);
								$this->basket['items'][] = current($this->productsModel->getBasketItems($newItems));

								break;
							}
						}
					}
					else
					{
						$newItems = $this->save($item);
						$this->basket['items'][] = current($this->productsModel->getBasketItems($newItems));
					}

				}
				else
				{
					$newItems = $this->save($item);
					$this->basket['items'][] = current($this->productsModel->getBasketItems($newItems));
				}

				/** Формируем идентификатор обработанного размера позиции для передачи js-бибиотеку */
				if(count($item[$id]) == 1)
				{
					list($size, $count) = explode('_', $item[$id][0]);
					if ($count > 0) {
						$selected = $id.'_'.str_replace('?', '', str_replace('/', '_', $size));
					}
				}
				//заполняем корзину
				$this->session->set('basket', array('items' => $this->basket['items']));
				if(!isset($this->basket)  || empty($this->basket['items']))
					$this->session->remove('informer');
			}
			else
				$this->basket['items'] = $this->session->get('basket');

			if(isset($this->basket) && !empty($this->basket['items']))
			{
				if(isset($this->basket['items']['items']))	$this->basket['items']	=	$this->basket['items']['items'];

				$mini	=	Catalogue::recountBasket($this->basket, $this->_shop['discounts']);
				$this->basket['items']	=	$mini['items'];
			}
			else
				$this->session->remove('informer');

			// Выдать ответ в JSON
			$this->setJsonResponse();

			// отключаю лишние представления
			$this->view->disableLevel([
				View::LEVEL_LAYOUT 		=> true,
				View::LEVEL_MAIN_LAYOUT => true
			]);

			//@upd Stanislav WEB чтобы работал ajax в minicart

			if(isset($mini)) $this->session->set('informer', $mini['informer']);
			//Set the content of the response
			$this->response->setJsonContent([
				'success' 		=>	true,
				'mode' 			=>	$this->request->getQuery('mode'),
				'hash' 			=>	$this->request->getQuery('hash'),
				'items' 		=>	(isset($mini)) ? $mini : [],
				'selected'		=>	$selected,
				'id' 			=> 	isset($id) ? $id : 0,
				'count'			=> 	(isset($mini)) ? $mini['informer']['count'] : 0,
				'discount'		=> 	(isset($mini['informer']['sum']) && isset($mini['informer']['discounts']) && $mini['informer']['discounts']['current'] > 0) ? $this->_translate['WITH_DISCOUNT'].' '.number_format($mini['informer']['sum'] - ($mini['informer']['sum']*$mini['informer']['discounts']['current']/100), 0, '.', ' ').' '.$this->_shop['currency_symbol'] : '',
				'basket_info'	=>	(isset($mini['informer']['count']) && $mini['informer']['count'] > 0) ?Catalogue::declOfNum($mini['informer']['count'], ['вещь', 'вещи', 'вещей'], true).' на '.number_format($mini['informer']['sum'], 0, '.', ' ').' '.$this->_shop['currency_symbol']: '',
				'basket' 		=> 	$this->view->getRender('partials/basket', $mode, [
					'items'		=>	$this->basket['items'],
					'basket'	=>	$this->session->get('informer'),
					'informer'	=>	$this->session->get('informer'),
					'get'		=>	$this->request->getQuery('item'),
				]),
			]);
			$this->response->send();

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

			if(null === $basket_items)
				return $this->session->get('basket');

			if ($this->session->has('basket') && $this->session->get('basket') != '' && null !== $this->session->get('basket')) {
				$product_id = key($basket_items);

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

				} else
				{
					//добавляем новую вещь

					$items = Catalogue::recountBasketSizes($basket_items);
				}
				return $items;
			}
			else
			{
				// первое вхождение в корзину.. если она была пуста
				$items = Catalogue::recountBasketSizes($basket_items);
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