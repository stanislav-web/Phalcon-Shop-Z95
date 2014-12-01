<?php
namespace Modules\ZKZ\Controllers;
use \Phalcon\Mvc\View,
	Helpers\Cart,
	Helpers\Catalogue;

/**
 * Class CartController Корзина
 *
 * Доступ к моделям
 *
 * @var $this->shopModel
 * @var $this->productsModel
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
 * @package Cart
 * @subpackage Controllers
 */
class CartController extends ControllerBase
{

	/**
	 * Объект с которым работаю
	 * используется для инициализации локалей и вызовов связанных классов
	 *
	 * @const string
	 */
	const	OBJECT	=	'cart';

	private

		/**
		 * @var array
		 * @see $this->setItems(array $items..or string $item);
		 * @access private
		 */
		$_items = [],

		/**
		 * @var string
		 * @see $this->setStatus(string $status);
		 * @access private
		 */
		$_status		=	null,

		/**
		 * @var string
		 * @see $this->setTemplate(string $template);
		 * @access private
		 */
		$_template		=	null,

		/**
		 * Будет ли ответ в json ?
		 * Только для ajax  вызовов
		 * @var bool
		 * @access private
		 */
		$_isJsonResponse	=	false;

	/**
	 * Метод обработки данных корзины
	 * @var string
	 * @access public
	 */
	public $method	=	'POST';

	/**
	 * initialize() Инициализация конструктора
	 *
	 * @access public
	 * @return null
	 */
	public function initialize()
	{
		// загрузка родителя
		parent::initialize();

		// загрузка локали и конфигурации
		$this->loadCustomTrans(self::OBJECT);

		// загрузка конфигураций корзины
		$this->setConfig($this->_config);

		if ($this->request->isAjax() && $this->request->getMethod() == $this->method)  // устанавливаю, что ответ будет в Json
			$this->_isJsonResponse = true;
		else
		{
			// обычная загрузка корзины
			$this->tag->setTitle($this->_shop['title']);
			$this->_isJsonResponse = false;
		}
	}

	/**
	 * initialize() Инициализация конструктора
	 *
	 * @access public
	 * @return null
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
		{
			//@modify организовую пересчет цен. получаю текущие product_id

			$calcItems = Catalogue::arrayToAssoc($basket['items'], 'product_id');
			$freshPrices  = $this->pricesModel->get(['product_id', 'price', 'discount'], ['product_id' => array_keys($calcItems), 'id' => $this->_shop['price_id']]);
			$basket['items'] = Catalogue::comparePrice($freshPrices, $calcItems);
			$this->session->set('basket', $basket);
			$items	=	Catalogue::recountBasket($basket, $this->_shop['discounts']);
		}
		else $items	=	$basket;

		$this->session->set('informer', $items['informer']);
		$this->view->setVars([
			'title'		=>	$title,
			'basket' 	=> 	(isset($items)) ? $items : [],
		]);
	}

	/**
	 * Добавление обновление корзины
	 */
	public function setAction()
	{
		if($this->_isJsonResponse)
		{
			// получаю POST данные
			$postData = $this->request->getPost();
			$sessionData = $this->session->get('cart');

			if(isset($postData['product_id']) && !empty($postData['size']))
			{
				// проверка переполнения
				if(sizeof($sessionData['items']) >= $this->_config->limitMax)
					return $this->json(['message' => $this->setMessage('Превышен лимит добавления. Не более '.$this->_config->limitMax)]);

				// проверяю входящие данные и узнаю новую цену

				//$items  = $this->pricesModel->get(['product_id', 'price', 'discount'], ['product_id' => array_keys($calcItems), 'id' => $this->_shop['price_id']]);


			}
			else
				return $this->json(['message' => $this->setMessage('Переданы не верные данные')]);

			$this->json([
				'hash'		=>	$this->request->getQuery('hash'),
				'mode'		=>	$this->request->getPost('mode')
			], $this->request->getPost('mode'));
		}
	}

	/**
	 * Загрузка конфигурации для корзины
	 * @param \Phalcon\Config $config
	 */
	public function setConfig(\Phalcon\Config $config)
	{
		$this->_config	=	$config->cart;
	}

	/**
	 * Контейнер нотификаций
	 * @param mixed $message
	 */
	public function setMessage($message)
	{
		if(!is_array($message))
			$body	=		$message;

		return	[
			'title'	=>	(isset($message['title'])) 	? $message['title'] 	: $this->_translate['CART'],
			'body'	=>	(isset($message['body'])) 	? $message['body'] 		: $body,
			'class'	=>	(isset($message['class'])) 	? $message['class'] 	: 'error',
		];
	}

	/**
	 * access public Парсер array to json
	 * @param array $content
	 */
	public function json(array $content, $template = null)
	{
		// Выдать ответ в JSON
		$this->setJsonResponse();

		// отключаю лишние представления
		$this->view->disableLevel([
			View::LEVEL_LAYOUT 		=> true,
			View::LEVEL_MAIN_LAYOUT => true
		]);

		if($template)
			$content = array_merge($content, ['cart' => $this->view->getRender('partials/cart', $template)]);

			$this->response->setJsonContent($content);

		// отправляю ответ
		$this->response->send();
	}
}