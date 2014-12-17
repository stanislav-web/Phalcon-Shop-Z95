<?php
namespace Modules\ZKZ\Controllers;
use \Helpers\Catalogue,
	\Phalcon\Mvc\View,
	\Mailer;

/**
 * Class JsonController API мост для ajax
 *
 * Доступ к моделям
 *
 * @var $this->productsModel
 * @var $this->tagsModel
 *
 * @var $this->session      вызов сессии
 * @var $this->request      информация об HTTP запросах
 * @var $this->router       посмотреть параметры текущего роута, настроить роуты
 *
 * @package Shop
 * @subpackage Controllers
 */

class JsonController extends ControllerBase
{
	private

		/**
		 * Текущий PATH из Url
		 * @var bool
		 */
		$requestUri			=	false,

		/**
		 * Ответ от сервера
		 * @var bool
		 */
		$_isJsonResponse	=	false;


	/**
	 * initialize() Инициализирую конструктор
	 * @access public
	 * @return null
	 */
	public function initialize()
	{
		parent::initialize();

		if($this->request->isAjax() == false) {

			$this->_isJsonResponse	=	false;
			//$this->flash->error('The request is not ajax');
			//die;
		}
		else	// устанавливаю, что ответ будет в Json
			$this->_isJsonResponse	=	true;

		// текущий request_uri
		$this->requestUri	=	$this->session->get('request_uri');
	}

	/**
 	 * tagsAction() Экшн выдачи тегов для запроса по ajax
 	 *
 	 * @access public
 	 * @author Stanislav WEB
 	 * @return json
 	*/
	public function tagsAction()
	{
		if($this->_isJsonResponse)
		{
			// Выдать ответ в JSON
			$this->setJsonResponse();

			// отключаю лишние представления
			$this->view->disableLevel([

				View::LEVEL_LAYOUT 		=> true,
        		View::LEVEL_MAIN_LAYOUT => true
			]);

			// получаю теги для выбранной категории+товары
			$category = $this->session->get('category');

			if(!empty($category))
			{
				// выгребаю теги
				$tags = $this->tagsModel->getTags($category['id'], true);

				// return ['sizes' => ..., 'tags' => ...]
				$tagsCloud = Catalogue::tagsToTree($tags);

				// выгребаю бренды
				//@notice возможно стоит сделать проверку, если есть теги, то не делаем запускаем бренды в фильтр
				$tagsCloud['brands'] = $this->brandsModel->getBrandsByCategory($category['id'], true);
			}
			else
			{
				// Выдача сайд бара для виртуалок, выдаю категории с подсчетом количества по сумме в дочерних категориях

				$sex = $this->session->get('sex'); // получаю пол
				if(null != $sex)
				{
					$genderCategories = Catalogue::categoriesToTree(
						Catalogue::arrayToAssoc(
							Catalogue::findInTree($this->_shopCategories, 'sex', "$sex"), 'id')
						, 0, false, 'count_products');

					$tagsCloud['categories'] = $genderCategories;
				}
					else
						$tagsCloud['categories'] = Catalogue::categoriesToTree(Catalogue::arrayToAssoc($this->_shopCategories, 'id'), 0, false, 'count_products');

			}
			// сортировка внутри сайд бара между секциями.. по количеству товаров внутри

			if(isset($tagsCloud['categories']) && !empty($tagsCloud['categories']))
				$tagsCloud['categories']['count'] 	= Catalogue::arraySum($tagsCloud['categories'], 'count_products');

			if(isset($tagsCloud['tags']) && !empty($tagsCloud['tags']))
				$tagsCloud['tags']['count'] 		= Catalogue::arraySum($tagsCloud['tags'], 'count_products');

			if(isset($tagsCloud['brands']) && !empty($tagsCloud['brands']))
				$tagsCloud['brands']['count'] 		= Catalogue::arraySum($tagsCloud['brands'], 'count_products');

			if(isset($tagsCloud['sizes']) && !empty($tagsCloud['sizes']))
				$tagsCloud['sizes']['count'] 		= Catalogue::arraySum($tagsCloud['sizes'], 'count_products');

			$tagsCloud = Catalogue::arraySort($tagsCloud, 'count', true, true);

			$this->response->setJsonContent([
				'response'	=>	$this->view->getRender('partials/json', 'tags', [
					'tagsCloud'		=>	$tagsCloud,
					'query'			=>	$this->session->get('query'), // параметры в адресной строке ?tags[]=246&tags[]=456 || brand[]=234&brand[]=43
					'request_uri'	=>	$this->requestUri,	// для сброса фильтров
				])
			]);

			// отправляю ответ
			$this->response->send();
		}
	}

	/**
	 * favoritesAction() Экшн для коллекции добавления в избранное по ajax
	 *
	 * @access public
	 * @author Stanislav WEB
	 * @return json
	 */
	public function favoritesAction()
	{
		$item_id = $this->request->get('item');
		if(isset($item_id) && is_numeric($item_id))
		{
			if($this->_isJsonResponse)
			{
				// Выдать ответ в JSON
				$this->setJsonResponse();

				// отключаю лишние представления
				$this->view->disableLevel([

					View::LEVEL_LAYOUT 		=> true,
					View::LEVEL_MAIN_LAYOUT => true
				]);

				// выбираю всю коллекцию пользователя
				$favorites = (array)$this->session->get('favorites');

				if(isset($favorites[$item_id])) // удаляю из избранного
				{
					unset($favorites[$item_id]);
					$status = 0;
				}
				else 	// добаляю в избранное
					$favorites[$item_id]	=	$status = 1;

				$this->session->set('favorites', $favorites);

				$this->response->setJsonContent([
					'id'		=>	$item_id,
					'count'		=>	sizeof($this->session->get('favorites')),
					'status'	=>	$status,
					'favorites'	=>	$favorites
				]);

				// отправляю ответ
				$this->response->send();
			}
		}
	}

	/**
	 * feedbackAction() Обработка формы обратной связи
	 *
	 * @access public
	 * @author Stanislav WEB
	 * @return json
	 */
	public function feedbackAction()
	{
		if($this->request->isPost() == true) // Только для POST запросов
		{
			if($this->_isJsonResponse)
			{
				// Загружаю локализацию для контроллера
				$this->loadCustomTrans('json');

				// Выдать ответ в JSON
				$this->setJsonResponse();

				// отключаю лишние представления
				$this->view->disableLevel([
					View::LEVEL_LAYOUT 		=> true,
					View::LEVEL_MAIN_LAYOUT => true
				]);

				// работаю с отправкой

				if($this->security->checkToken())
				{
					$post = $this->request->getPost();
					if(!empty($post['user_fio']) && !empty($post['message']))
					{
						// подключение Swift Mailer
						require_once APP_PATH.'/library/Mailer/Swiftmailer/lib/swift_required.php';

						$mailer = new Mailer\Manager((array)$this->_config->mailer);
						$message = $mailer->createMessage()
							->to($this->_config->mailer->to->email, $this->_config->mailer->to->name)
							->subject($this->_translate['FEEDBACK_SUBJECT'])
							->content($post['message']);

						// отправка
						$status = $message->send($message);

						if($status)	$response = ['status'	=>	1,
							'message'	=>	$this->_translate['MESSAGE_SUCCESS'],
							'console'	=>	sprintf("Sent %d messages\n", $status)
						];
						else $response = [ 'status'	=>	0,
							'message'	=>	$this->_translate['MESSAGE_FAILED']
						];
					}
					else	$response = ['status'	=>	0,
						'message'	=>	$this->_translate['EMPTY_REQUIRED_FIELDS']
					];
				}
				else
					$response = ['status'	=>	0,
						'message'	=>	$this->_translate['FISHING_DETECTED']
					];

				$this->response->setJsonContent($response);
				$this->response->send();
			}
		}
		else
			$this->dispatcher->forward([
				'controller' 	=> 'error',
				'action'     	=> 'show404',
			]);
	}

	/**
	 * hotlineAction() Треккер горячих покупок
	 *
	 * @access public
	 * @author Stanislav WEB
	 * @return json
	 */
	public function hotlineAction()
	{
		if($this->request->isGet() == true) // Только для POST запросов
		{
			if($this->_isJsonResponse)
			{
				// Загружаю локализацию для контроллера
				$this->loadCustomTrans('json');

				// Выдать ответ в JSON
				$this->setJsonResponse();

				// отключаю лишние представления
				$this->view->disableLevel([
					View::LEVEL_LAYOUT 		=> true,
					View::LEVEL_MAIN_LAYOUT => true
				]);

				// собираю параметры запроса
				$months	=	1;
                $cityDef = $this->request->getServer('HTTP_GEOIP_CITY');
                if($cityDef === 'None')
                    $cityDef    =   $this->_shop['capital_city'];
				$request	=	[
					'start'		=>	time() - $months*30*24*60*60,			//	начальная дата (1  мес. ранее)
					'end'		=>	time(),							//	текущая дата
					'city'		=>	$cityDef,	//	город для которого смотрим заказы
					'lines'		=>	20,								//	количество строк
					'shop_id'	=>	$this->_shop['id']				//	id магазина
				];

				//@response -->
				$response = (new \API\APIClient($this->_shop['token_key']))
					->setURL('http://back95.ru/api/jsonrpc/')
                    ->debug(false)
					->call('hotline.get', $request['start'], $request['end'],$request['city'],$request['lines'],$request['shop_id']);

				// обработка ответа
				if(isset($response['result']))
				{
					$response = $response['result'];
					// Если город не определили или если заказов для города нет — выводим заказы для столицы (Астана, Киев, Москва....).
					if(empty($request['city']) || $response['orders_count'] == 0)
					{
						$request['city']	=	$this->_shop['capital_city']; // а вот сдесь уже выбираю столицу, если по городу не нашли
						$use_capital = true;

						// повторяю пинг
						$response = (new \API\APIClient($this->_shop['token_key']))->call('hotline.get', $request['start'], $request['end'],$request['city'],$request['lines'],$request['shop_id']);
					}

					if($response['orders_count'])
					{
						if(isset($response['buy']) && is_array($response['buy']) && count($response['buy']) > 0 )
						{
							$buy_items = Catalogue::arrayToAssoc($response['buy'], 'cat_id');
							$ids = array_keys($buy_items);

							// получаю товары из базы по купленным
							$items = $this->productsModel->get([], ['product_id' => $ids], [], null, true);
						}
					}
				}

				// отправка ответа в Json
				$this->response->setJsonContent([
					'json'	=>	$this->view->getRender('partials/json', 'hotline', [
						'shop'			=>	$this->_shop,
						'orders_count'	=>	(isset($response['orders_count'])) ? $response['orders_count'] : 0,
						'items'			=>	(isset($items)) ? $items : [],
						'buy_items'		=>	(isset($buy_items)) ? $buy_items : [],
						'categories'	=>	$this->_shopCategories,
						'params' => [
							'city'			=>	$request['city'],								// запрошеный город
							'use_capital'	=>	(isset($use_capital)) ? $use_capital : false,	// столица ?
							'month_count'	=>	$months,										// количество месяцев
							'num_factor'	=>	1,												// множитель кол-ва заказов :-) в идеале оставить 1-кой...
						]
					]),
					'target'=> 	'hot_line'
				]);

				$this->response->send();
			}
		}
		else
			$this->dispatcher->forward([
				'controller' 	=> 'error',
				'action'     	=> 'show404',
			]);
	}
}

