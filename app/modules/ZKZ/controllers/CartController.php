<?php
namespace Modules\ZKZ\Controllers;
use \Phalcon\Mvc\View,
	Helpers\Cart;

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

			// Получаю баннер для страницы
			$banner = $this->bannersModel->getBanners($this->_shop['id'], true);

			// ну и баннер на каждой странице ))
			$this->view->setVar('banner', $banner);
			$this->_isJsonResponse = false;
		}
	}

	/**
	 * Выдача в главной корзине
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

		if($this->session->has('cart') && !empty($this->session->get('cart')['items']))
			$this->view->setVars($this->session->get('cart'));
		else
			$this->session->remove('cart');

		$this->view->setVar('title', $title);
	}

	/**
	 * Добавление обновление, перекалькуляция корзины
	 */
	public function setAction()
	{
		if($this->_isJsonResponse)
		{
			// получаю POST данные
			$postData = $this->request->getPost();
			$sessionData = $this->session->get('cart');

			if(isset($postData['mode']) && $this->request->has('hash'))
			{
				if($postData['action'] != 'read')
				{
					// проверка переполнения корзины
					if(Cart::overflowItems(@$sessionData['items'], $this->_config->limitMax))
						return $this->json(['message' => $this->setMessage('Превышен лимит добавления. Не более '.$this->_config->limitMax)]);

					// проверка лимита на размеры в корзине
					if(Cart::overflowSizes(@$sessionData['items'], $this->_config->limitOne))
						return $this->json(['message' => $this->setMessage('Превышен лимит добавления размера. Не более '.$this->_config->limitOne.' размеров для этой вещи')]);
				}

				// получаю вещи со свежими ценами (существующие всегда перезаписываются)
				if(isset($sessionData['items']) || isset($postData['product_id']))
					$dbData = $this->productsModel->get(
						['prod.id as product_id', 'prod.name', 'prod.articul', 'prod.images', 'prod.filter_size', 'price.price', 'price.discount', 'brand.name as brand_name',
						'price.price', 'price.discount', 'price.percent'],
						['product_id' => Cart::pushItem(@$sessionData['items'], @$postData['product_id'])]
					);
				else $dbData	=	[];

				// добавляю (обновляю размеры у вещей)
				$cart = Cart::filter($dbData, $postData, $sessionData);
				// теперь проверяю содержимое по скидкам магазина (если они есть и еще действуют)

				if(!empty($this->_shop['discounts']) && isset($cart['meta']['total']) > 0)
					$cart['meta']['shop_discounts'] = Cart::getMaxDiscount($this->_shop['discounts'], $cart['meta']);
					$cart['meta']['action']	=	$this->request->getPost('action');

				// ну и перезаписываю данные сессии
				if(!empty($cart['items']))
					$this->session->set('cart', $cart);
				else
					$this->session->remove('cart');
			}
			else
				return $this->json(['message' => $this->setMessage('Переданы не верные данные')]);

			$this->json([
				'hash'		=>	$this->request->getQuery('hash'),
				'mode'		=>	$this->request->getPost('mode'),
				'minicart'	=>	$this->session->get('cart')['meta']
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
		$this->view->setVar('config', $this->_config);
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
	 * @param array template
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
			$content = array_merge($content, ['cart' => $this->view->getRender('partials/cart', $template, $this->session->get('cart'))]);

			$this->response->setJsonContent($content);

		// отправляю ответ
		$this->response->send();
	}
}