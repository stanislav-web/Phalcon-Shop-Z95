<?php
namespace API;

use Phalcon\Http\Response;
/**
 * Class APIClient FrontEnd -> Backend PORT
 * @package Phalcon
 * @subpackage API
 */
class APIClient {

	private

		/**
		 * The request id
		 * @var integer
		 */
		$id	=	1,

		/**
	 	 * Debug state
	 	 * @var boolean
	 	 */
		$debug,

		/**
		 * Phalcon response handler
		 * @var \Phalcon\Http\Response
		 */
		$response = false,

		/**
		 * If true, notifications are performed instead of requests
		 * @var boolean
		 */
		$notification = false,

		/**
		 * Token tracking
		 * @var boolean
		 */
		$token = null,

		/**
		 * The server URL
		 * @var string
		 */
		$url	= false;

	/**
	 * Shop token configure
	 * @param string $token
	 * @param bool   $debug
	 */
	public function __construct($token = null)
	{
		if(!$this->response)
			$this->response = new Response();

		$this->token = $token;

		// debug state
		empty($debug) ? $this->debug = false : $this->debug = true;
	}

	/**
	 * setURL($url) Connection URI
	 * @param $url
	 * @return $this
	 */
	public function setURL($url)
	{
		$this->url	=	$url;
		return $this;
	}

	/**
	 * debug(boolean $debug) enable Debug ?
	 * @param boolean $debug
	 * @return $this
	 */
	public function debug($debug)
	{
		$this->debug	=	$debug;
		return $this;
	}

	/**
	 * Get notification
	 * @param boolean $notification
	 * @return object this
	 */
	public function setRPCNotification($notification)
	{
		empty($notification) ?
			$this->notification = false
			:
			$this->notification = true;

		return $this;
	}

	/**
	 * Performs a jsonRCP request and gets the results as an array
	 *
	 * @param string $method
	 * @param array $params
	 * @return array
	 */
	public function call()
	{
		$params = func_get_args();
		$method = array_shift($params);

		// check
		if(!is_scalar($method))
			throw new \Phalcon\Exception('Method name has no scalar value');

		// check
		if(is_array($params))
		{
			// no keys
			$params = array_values($params);
		}
		else
			throw new \Phalcon\Exception('Params must be given as array');

		array_unshift($params,$this->token);

		// sets notification or request task
		if($this->notification)
			$currentId = NULL;
		else
			$currentId = $this->id;

		// prepares the request
		$request = $this->response->setJsonContent([
			'method' 	=> $method,
			'params' 	=> $params,
			'id' 		=> $currentId
		]);

		$request = $request->getContent();
		if($this->debug) $this->debug  =	"\n\n***** Request *****\n".$request."\n***** End Of request *****\n\n";

		// performs the HTTP POST
		$opts = array ('http' => array (
			'method'  => 'POST',
			'header'  => 'Content-type: application/json',
			'content' => $request
		));

		$context  = stream_context_create($opts);
		if($fp = fopen($this->url, 'r', false, $context))
		{
			$response = '';
			while($row = fgets($fp)) {
				$response.= trim($row)."\n";
			}
			if($this->debug) $this->debug.= "***** Server response *****\n".$response."\n***** End of server response *****\n\n";
			$debug = $response;
			$response = json_decode($response,true);
		}
		else
			throw new \Phalcon\Exception('Unable to connect to '.$this->url);

		if(!$this->notification)
		{
			if($response['id'] != $currentId)
				throw new \Phalcon\Exception('Incorrect response id (request id: '.$currentId.', response id: '.$response['id'].')'."\n".$debug);

			if(isset($response['error']) && !is_null($response['error']))
				throw new \Phalcon\Exception('Request error: '.$response['error']);

			if($this->debug)
				$response['debug']	=	$this->debug;

			return $response;
		}
		else
			return true;
	}
}