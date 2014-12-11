<?php
namespace Modules\ZKZ\Controllers;

use API\APIClient;

class TestController extends ControllerBase
{
	private
		$_api,
		$_response	=	null;

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
	}

	/**
	 * Выдача в главной корзине
	 * @access public
	 * @return null
	 */
	public function indexAction()
	{
		try {

			// get access token
			$this->view->disable();
			// initialize API client
			$this->_api = new APIClient();

			$this->_api
				->setToken('435c4d614fcdcf443f433f2469920f35')
				->setURL('http://b.stanislavw.dev95.ru/api/jsonrpc/');

			$this->_response = $this->_api->call('catalogue.synx', [
				'last_update' 	=> 	date("Y-m-d H:i:s", time()-234),
				'shops' 		=> 	true,
				'products' 		=> 	true,
				'prices' 		=> 	true,
				'categories' 	=> 	true,
				'brands' 		=> 	true,
			]);

			$result = $this->_response['result'];
			print_r($result);
			exit('111');
		}
		catch(\Phalcon\Exception $e)
		{
			echo $e->getMessage();
		}
	}


}