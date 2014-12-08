<?php
use API\APIClient,
	Helpers\Cli;

/**
 * Class CatalogueTask Command Line Client
 *
 * Помошники
 * 	-	Cli::colorize($string, $status);
 *
 * Модели
 *  -	(new Shops())->get(['code'	=>	$this->router->getModuleName()],[], 1, true);

 *
 * @package CLI
 * @subpackage Tasks
 */
class CatalogueTask extends \Phalcon\CLI\Task
{
	private
		$_api,
		$_config,
		$_response	=	null;

	/**
	 * Initialize task
	 * @access public
	 */
	public function mainAction()
	{
		try {

			$this->_config	=	$this->di->get('config')['sync'];

			// initialize API client
			$this->_api = new APIClient();

			$this->_api->debug(true)
				->setToken($this->_config->token)
				->setURL($this->_config->url);

			if($this->_api)
				echo Cli::colorize('API connected success', 'SUCCESS');

			// get another action
			$this->console->handle([
				'task' 		=> 'catalogue',
				'action' 	=> 'pool'
			]);
		}
		catch(Phalcon\Exception $e) {
			echo Cli::colorize($e->getMessage(), 'FAILURE');
		}
	}

	/**
	 * Pooling server
	 * @access public
	 */
	public function poolAction()
	{
		try {

			// initialize API client
			$this->_response = $this->_api->call('catalogue.export', [
				'timestamp' 	=> 	time()-$this->_config->delay,
				'start' 		=> 	0,
				'limit' 		=> 	$this->_config->limit,
				'categories' 	=> 	true,
			]);

			if(!empty($this->_response))
			{
				echo Cli::colorize('Received a response from the '.parse_url($this->_config->url, PHP_URL_HOST), 'SUCCESS');

				if(!empty($this->_response['items']))
				{
					// get another action
					$this->console->handle([
						'task' 		=> 'catalogue',
						'action' 	=> 'handle'
					]);
				}
				else
					die(Cli::colorize('Items not found', 'FAILURE'));
			}
			else
				die(Cli::colorize('Failed a response from the server', 'FAILURE'));
		}
		catch(Phalcon\Exception $e) {
			echo Cli::colorize($e->getMessage(), 'FAILURE');
		}
	}

	/**
	 * Handle response
	 * @access public
	 */
	public function handleAction()
	{
		try {


		}
		catch(Phalcon\Exception $e) {
			echo Cli::colorize($e->getMessage(), 'FAILURE');
		}
	}
}