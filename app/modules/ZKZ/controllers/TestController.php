<?php
namespace Modules\ZKZ\Controllers;

use API\APIClient,
	Helpers\Cli;

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

			$this->_api->debug(true)
				->setToken('435c4d614fcdcf443f433f2469920f35')
				->setURL('http://b.maggadda.dev95.ru/api/jsonrpc/');

			$this->_response = $this->_api->call('catalogue.export2', [
				'timestamp' 	=> 	time()-45454,
				'start' 		=> 	0,
				'limit' 		=> 	1000,
				'categories' 	=> 	true,
			]);

			$result = json_decode($this->_response['result'], true);
			print_r($result);
		}
		catch(\Phalcon\Exception $e)
		{
			echo $e->getMessage();
		}
	}


}