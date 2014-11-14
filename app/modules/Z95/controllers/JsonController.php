<?php
namespace Modules\Z95\Controllers;
use \Helpers\Catalogue,
	\Mappers\Router,
	\Phalcon\Mvc\View;

/**
 * Class JsonController API мост для ajax
 *
 * Доступ к моделям
 *
 * @var $this->productsModel
 * @var $this->tagsModel
 * @var $this->commonModel
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
			$this->flash->error('The request is not ajax');
			$this->_isJsonResponse	=	true;
			return false;
		}
		else	// устанавливаю, что ответ будет в Json
			$this->_isJsonResponse	=	true;

		// текущий request_uri
		$this->requestUri	=	$this->request->getURI();
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
			}
			else
			{
				// выдача тегов для виртуалок
			}

			// выгребаю бренды
			//@notice возможно стоит сделать проверку, если есть теги, то не делаем запускаем бренды в фильтр
			$tagsCloud['brands'] = $this->brandsModel->getBrandsByCategory($category['id'], true);

			$this->response->setJsonContent([
				'response'	=>	$this->view->getRender('partials/json', 'tags', [
					'tagsCloud'	=>	$tagsCloud
				])
			]);

			// отправляю ответ
			$this->response->send();
		}
	}

	/**
	 * setJsonResponse() Установка режима выдачи ответа в JSON
	 * @access public
	 * @return null
	 */
	public function setJsonResponse()
	{
		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');
	}
}

