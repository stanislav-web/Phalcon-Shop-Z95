<?php
namespace Modules\ZKZ\Controllers;

use \Phalcon\Mvc\View,
	\Helpers;

/**
 * Class OrderController Оформление заказа
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
 * @var $this->_mainCategories главные категории

 *
 * @var $this->di           вызов компонентов из app/config/di.php
 * @var $this->session      вызов сессии
 * @var $this->request      информация об HTTP запросах
 * @var $this->router       посмотреть параметры текущего роута, настроить роуты
 *
 * @package Order
 * @subpackage Controllers
 */

class OrderController extends ControllerBase
{

	private
		/**
		 * Тип запрос - ответа
		 * @var bool
		 */
		$_isJsonResponse	=	false,

		/**
	 	 * Баннера
	 	 * @var bool
	 	 */
		$_banners			=	false,

		/**
		 * Страны, которые в обслуживании
		 * @var bool
		 */
		$_countries			=	[];

	/**
	 * initialize() Инициализирую конструктор
	 * @access public
	 * @return null
	 */
	public function initialize()
	{
		// устанавливаю шаблон и загружаю локализацию
		$this->loadCustomTrans('order');
		parent::initialize();

		if($this->request->isAjax() == false)
		{
			$this->tag->setTitle($this->_shop['title']);
			$this->_isJsonResponse	=	false;

			$this->_countries	=	[
				'kz'	=>	$this->_translate['COUNTRIES']['kz'],
				'ru'	=>	$this->_translate['COUNTRIES']['ru'],
				'ua'	=>	$this->_translate['COUNTRIES']['ua']
			];

			// Получаю баннер для страницы
			$this->_banners = $this->bannersModel->getBanners($this->_shop['id'], true);

			// ну и баннер на каждой странице ))
			$this->view->setVar('banners', $this->_banners);


		}
		else	// устанавливаю, что ответ будет в Json
			$this->_isJsonResponse	=	true;
	}

	/**
	 * Страница оформления заказов
	 * @author Stanislav WEB
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

		$this->view->setVar('title',$title);

		if(!$this->session->has('basket'))	// если сессия с корзиной пуста
			$this->view->setVars(['template' => 'empty']);
		else
								// если корзина еще не очищена, загружаю форму заказа
			$this->view->setVars([
				'template' 	=> 'order_form',
				'form' 		=> new Helpers\OrderForm(null, [
					'delivery'	=>	$this->_shop['delivery_ids'],
					'payment'	=>	$this->_shop['payment_ids'],
					'country'	=>	$this->_shop['country_code'],
					'select'	=>	($this->session->has('user')) ? $this->session->get('user') : false
				]), // передаю параметры сессии пользователя для автозаполнения полей формы
				'order_form'	=>		($this->session->has('user')) ? $this->session->get('user') : false,
			]);
	}

	/**
	 * Страница трекинга заказа
	 * @author Stanislav WEB
	 * @access public
	 */
	public function trackingAction()
	{
		// получаю параметр $tracking_id
		$tracking_id = $this->dispatcher->getParam("tracking_id");

		if(isset($tracking_id))
		{
			$this->_breadcrumbs->reset();
			$this->_breadcrumbs
				->add($this->_translate['MAIN'], '/');


			// если зашли первый раз (те. сразу после оформления заказа)
			if($this->session->has('order_processing'))
			{
				$this->session->remove('order_processing');

				// вывожу сообщение об успешном оформлении заказа
				$title	=	$this->_translate['ORDER_SUCCESS'];
				$template	=	'success';

				// Добавляю путь в цепочку навигации
				$this->_breadcrumbs->add($this->_translate['TITLE'], $this->request->getURI());
			}
			else
			{
				// Запрашиваем информацию о заказе на бекенде

				try {

					//@response -->
					$customer = (new \API\APIClient($this->_shop['token_key']))
						->setURL('http://b.dev95.ru/api/jsonrpc/')
						->call('customer.get1', ['tracking_id' => $tracking_id])['result'];

					if(!empty($customer['order']['order_id']))
					{
						$order = $customer['order'];
						if(!isset($order['paid_status'])) $order['paid_status'] = 0;

						if($order['shop']['payment'] == 8 && $order['paid_status'] == 0)
							$title = $this->_translate['STATUSES']['PROCEED_TO_PAYMENT'];
						else
							$title = sprintf($this->_translate['ORDER_TITLE'], $order['order_code'], $order['status']);

						// Запоминаем авторизованного пользователя
						$this->session->set('customer_id', $customer['user_id']);
						if($this->session->has('refer'))
							$order['refer'] = $this->session->get('refer');

						$this->session->remove('refer');

						$this->view->setVars([
							'customer' 	=> 	$customer,
							'countries'	=>	$this->_countries
						]);
					}
					else
						$title	=	$this->_translate["ORDERS"];

					// работа с трекингом заказа
					$template	=	'tracking';

					// Добавляю путь в цепочку навигации
					$this->_breadcrumbs->add($this->_translate['ORDERS'], $this->request->getURI());
				}
				catch(\Phalcon\Exception $e)
				{
					echo $e->getMessage();
				}
			}

			$this->tag->prependTitle(strip_tags($title).' - ');

			$this->view->setVars([
				'title' 		=> $title,
				'template' 		=> $template,
				'tracking_id'	=> $tracking_id
			]);

			// ссылаюсь на вывод в action index с видом order/index
			$this->view->render('order', 'index')->pick("order/index");
		}
	}

	/**
	 * orderAction() Отправка заказа на Backend
	 *
	 * @access public
	 * @author Stanislav WEB
	 * @return json
	 */
	public function requestAction()
	{
		if($this->request->isPost() == true) // Только для POST запросов
		{
			$request = (array)$this->session->get('basket');
			if(isset($request) && !empty($request['items']))
			{
				if($this->_isJsonResponse && $this->security->checkToken())
				{
					// Выдать ответ в JSON
					$this->setJsonResponse();

					// отключаю лишние представления
					$this->view->disableLevel([
						View::LEVEL_LAYOUT 		=> true,
						View::LEVEL_MAIN_LAYOUT => true
					]);

					try {

						// метка о начале трекинга

						$this->session->set('order_processing', '1');
						$order = $this->filter($request);

						//@response -->
						$response = (new \API\APIClient($this->_shop['token_key']))
							->setURL('http://b.dev95.ru/api/jsonrpc/')
							->call('orders.create', $order);

						if(!empty($response['result']))
						{
							$this->session->remove('basket');
							$this->session->remove('informer');

							$response['result']['status']		=	1;
							$response['result']['message']		=	$this->_translate['ORDER_SUCCESS'];
						}

						//@request <--

						$this->response->setJsonContent($response['result']);
						$this->response->send();
					}
					catch(\Phalcon\Exception $e)
					{
						echo $e->getMessage();
					}
				}
				else
				{
					$this->session->remove('order_processing');

					// Выдать ответ в JSON
					$this->setJsonResponse();

					$this->response->setJsonContent([
						'status'	=>	3,
						'message'	=>	$this->_translate['ORDER_FAILED'],
					]);
					$this->response->send();
				}
			}
		}
		else
			$this->dispatcher->forward([
				'controller' 	=> 'error',
				'action'     	=> 'show404',
			]);
	}

	/**
	 * Фильтр передаваемых в API параметров
	 *
	 * @param array $order
	 * @return array
	 */
	private function filter(array $order)
	{
		$result	=	[];

		if($this->request->isPost())
		{
			if($this->session->has('user'))
				$sessionUser = $this->session->get('user');

			if($this->request->hasPost('user'))
				$postUser = $this->request->getPost('user');

			// проверка USER_ID

			if(isset($user['user_id']))
				$result['user']['user_id']	=	$sessionUser['user_id'];
			elseif($this->session->has('customer_id'))
				$result['user']['user_id']	=	$this->session->get('customer_id');
			else
				$result['user']['user_id']	=	0;

			// проверка PHONES

			if(!empty($postUser['phones']))
				$result['user']['phones']	=	$postUser['phones'];
			elseif(!empty($sessionUser['phones']))
				$result['user']['phones']	=	$sessionUser['phones'];
			else
				$result['user']['phones']	=	0;

			// проверка EMAIL

			if(!empty($postUser['email']))
				$result['user']['email']	=	$postUser['email'];
			elseif(!empty($sessionUser['email']))
				$result['user']['email']	=	$sessionUser['email'];
			else
				$result['user']['email']	=	0;

			// проверка ФИО

			if(!empty($postUser['fio']))
				$result['user']['fio']	=	$postUser['fio'];
			elseif(!empty($sessionUser['fio']))
				$result['user']['fio']	=	$sessionUser['fio'];
			else
				$result['user']['fio']	=	'';

			// проверка ИНДЕКСА

			if(!empty($postUser['address']['postal_code']))
				$result['user']['address']['postal_code']	=	$postUser['address']['postal_code'];
			elseif(!empty($sessionUser['address']['postal_code']))
				$result['user']['address']['postal_code']	=	$sessionUser['address']['postal_code'];
			else
				$result['user']['address']['postal_code']	=	'';

			// проверка РЕГИОНА

			if(!empty($postUser['address']['region']))
				$result['user']['address']['region']	=	$postUser['address']['region'];
			elseif(!empty($sessionUser['address']['region']))
				$result['user']['address']['region']	=	$sessionUser['address']['region'];
			else
				$result['user']['address']['region']	=	'';

			// проверка ГОРОДА

			if(!empty($postUser['address']['city']))
				$result['user']['address']['city']	=	$postUser['address']['city'];
			elseif(!empty($sessionUser['address']['city']))
				$result['user']['address']['city']	=	$sessionUser['address']['city'];
			else
				$result['user']['address']['city']	=	'';

			// проверка АДРЕСА

			if(!empty($postUser['address']['address']))
				$result['user']['address']['address']	=	$postUser['address']['address'];
			elseif(!empty($sessionUser['address']['address']))
				$result['user']['address']['address']	=	$sessionUser['address']['address'];
			else
				$result['user']['address']['address']	=	'';

			// проверка ДОСТАВКИ

			$result['options']['delivery_id']	=	$this->request->getPost('options')['delivery_id'];

			// проверка ОПЛАТЫ

			$result['options']['payment_id']	=	$this->request->getPost('options')['payment_id'];

			// проверка КУПОНА

			$result['coupon_uniqid']			=	$this->request->getPost('coupon_uniqid');

			// проверка КОММЕНТАРИЯ

			$result['comment']					=	$this->request->getPost('comment');

			// служебная инфа из настроек системы

			$result['user']['address']['country']	=	$this->_shop['country'];
			$result['user']['subscriptions']		=	[$this->_shop['subscribe_id']];
			$result['lang']							=	$this->session->get("language");

			// проверка содержимого заказа
			$result['items'] = Helpers\Catalogue::orderFilterItems($order['items']);

			// проверка посещеных вещей
			if($this->session->has('visited_items'))
				$result['visited_items']	=	$this->session->get("visited_items");

			// проверка referrer
			if($this->session->has('ref'))
				$result['refer']	=	$this->session->get('ref');

			// проверка referrer data
			if($this->session->has('refer_data'))
				$result['refer_data']	=	$this->session->get('refer_data');
		}
		$this->session->set('user', $result);
		return $result;
	}
}
