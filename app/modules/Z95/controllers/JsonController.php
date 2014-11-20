<?php
namespace Modules\Z95\Controllers;
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
			$this->flash->error('The request is not ajax');
			die;
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
	 * orderAction() Отправка заказа на Backend
	 *
	 * @access public
	 * @author Stanislav WEB
	 * @return json
	 */
	public function orderAction()
	{
		if($this->request->isPost() == true) // Только для POST запросов
		{
			$order = (array)$this->session->get('basket');
			if(isset($order) && !empty($order['items']))
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

					// фильтрую только нужные параметры в заказе
					$items = Catalogue::orderFilterItems($order['items']);

					//@todo API CALL Here...

					$this->response->setJsonContent([
						'status'		=>	1, // статус ответа возвращает Backend
						'request'		=>	[
							'items'		=>	$items,
							'customer'	=> 	$this->request->getPost()
						]
					]);

					// отправляю ответ на бекенды
					// <-- получаю ответ, А ЗАТЕМ ОЧИСТИТЬ КОРЗИНУ (сессию) в случае success
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
	 * feedbackAction() Обработка формы обратной связи
	 *
	 * @access public
	 * @author Stanislav WEB
	 * @return json
	 */
	public function feedbackAction()
	{
		$response =	[];

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
}

