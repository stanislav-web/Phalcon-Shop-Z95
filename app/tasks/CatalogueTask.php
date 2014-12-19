<?php
use API\APIClient,
	Helpers\Cli;

/**
 * Class CatalogueTask Command Line Client
 *
 * Помошники
 * 	-	Cli::colorize($string, $status);
 *
 * @package CLI
 * @subpackage Tasks
 */
class CatalogueTask extends \Phalcon\CLI\Task
{
	private
		$_api,
		$_config,
		$_start		=	0,
		$_response	=	null,
		$_db;

	/**
	 * Initialize task
	 * @access public
	 */
	public function mainAction()
	{
		// enable time elapse
		$start	=	explode(" ", microtime());
		$this->_start = $start[1] + $start[0];

		try {

			$this->_config	=	$this->di->get('config')['sync'];

			// initialize API client
			$this->_api = new APIClient();

			$this->_api->setToken($this->_config->token)
				->setURL($this->_config->url);

			if($this->_api) {
				echo Cli::colorize(Cli::bold("[SUCCESS] API connected"), 'SUCCESS');
				echo Cli::colorize(Cli::bold("[INFO] Adapter ".$this->_config->adapter)."\n", 'WARNING');
			}

			// get another action
			$this->console->handle([
				'task' 		=> 'catalogue',
				'action' 	=> 'pool'
			]);
		}
		catch(Phalcon\Exception $e) {
			echo Cli::colorize("[FAIL] ".$e->getMessage(), 'FAILURE');
		}
	}

	/**
	 * Pooling server
	 * @access public
	 */
	public function poolAction()
	{

		// Get SQL connect
		$this->_db = $this->di->get('db');

		try {

			$system = $this->_db->fetchOne("SELECT * FROM system", Phalcon\Db::FETCH_ASSOC);

			// initialize API client
			$this->_response = $this->_api->call('catalogue.synx', [
				'last_update' 	=> 	(empty($system['value'])) ?
										date("Y-m-d H:i:s", time()-$this->_config->delay) :
										date("Y-m-d H:i:s", $system['value']-$this->_config->delay),
                //'brands' 		=> 	1,
                //'products' 	=> 	1,
                //'products_relationship'	=> 	1,
                //'prices' 	    => 	1,
                'shops' 		=> 	1,
                //'categories' 	=> 	1,
                //'category_shop_relationship' 	=> 	1,
				'decode'		=> 	$this->_config->decode,
				'adapter'		=> 	$this->_config->adapter,
				'limit'			=>	(isset($this->_config->limit)) ? $this->_config->limit : null,
			]);

			// fixed end queries time
			$time = explode(" ", microtime());

			if(!empty($this->_response['result']))
			{
				echo Cli::colorize("[INFO] Received a response from the ".parse_url($this->_config->url, PHP_URL_HOST).
					sprintf("\n[INFO] Time: %f sec.", (($time[1] + $time[0])-$this->_start))."\n[INFO] Size length: ".(memory_get_usage()/1024)." kb.\n"
					, 'WARNING');

				// get another action
				if($this->_config->checkonly === 1)
					$this->console->handle([
						'task' 		=> 'catalogue',
						'action' 	=> 'finish'
					]);
				else
					$this->console->handle([
						'task' 		=> 'catalogue',
						'action' 	=> 'prepare'
					]);
			}
			else
			{
				echo Cli::colorize(Cli::bold("[INFO] Items not found"), 'WARNING');

				// get another action
				$this->console->handle([
					'task' 		=> 'catalogue',
					'action' 	=> 'finish'
				]);
			}
		}
		catch(Phalcon\Exception $e) {
			echo Cli::colorize("[FAIL] ".$e->getMessage(), 'FAILURE');
		}
	}

	/**
	 * Prepare receiver items for save into DB
	 * @access public
	 */
	public function prepareAction()
	{
		try {

			if($this->_config->decode > 0)
				$this->_response['result'] = base64_decode($this->_response['result']);

			if(isset($this->_config->adapter) && $this->_config->adapter == 'json_encode')
				$this->_response	=	json_decode($this->_response['result'], true);
			else
            {
               $this->_response	=	unserialize($this->_response['result']);
            }

			try
			{
				if(!empty($this->_response))
				{
					// Start transaction
					$this->_db->begin();

					foreach($this->_response as $table => $attributes)
					{
						if(isset($table))
							$this->update($table, $attributes);
					}

					// Do the commit
					$this->_db->commit();
				}
				else
					echo Cli::colorize(Cli::bold("[INFO] Nothing to update"), 'WARNING');

				// get another action
				$this->console->handle([
					'task' 		=> 'catalogue',
					'action' 	=> 'finish'
				]);
			}
			catch(Phalcon\Exception $e)
			{
				$this->_db->rollback();

				// update synx time
				$this->_db->execute("UPDATE `system` SET `value` =	'".time()."' WHERE `key` = 'last_synx'");

				echo Cli::colorize(Cli::bold("[INFO] All updates was rollback by transaction"), 'WARNING');
				echo Cli::colorize("[FAIL] ".$e->getMessage(), 'FAILURE');
				return false;
			}
		}
		catch(Phalcon\Exception $e) {

			// update synx time
			$this->_db->execute("UPDATE `system` SET `value` =	'".time()."' WHERE `key` = 'last_synx'");
			echo Cli::colorize("[FAIL] ".$e->getMessage(), 'FAILURE');
		}
	}

	/**
	 * Updating data
	 *
	 * @param $table table name
	 * @param $fields fields [name => value]
	 * @access public
	 * @throws Exception
	 */
	public function update($table, $fields)
	{
		$success = 0;
		foreach($fields as $value)
		{
			$sql	=	"INSERT INTO ".$table." (".implode(', ',array_keys($value)).") VALUES (".implode(", ",

						array_map(function($v) {
                                if($v != 'NULL')
                                    $v = $this->_db->escapeString($v);
							return $v;
						},
						array_values($value)
					))
				.") ON DUPLICATE KEY UPDATE ";
			foreach($value as $k => $v)
            {
                if($v != 'NULL')
                    $v  = $this->_db->escapeString($v);
                $sql .="`$k` = ".$v.",";
            }

			$sql = trim(rtrim($sql, ','));

            //print "\r\n".$sql."\r\n"; exit;
			$status = $this->_db->execute($sql);

			if($status)
				++$success;

            unset($sql);
		}

		echo Cli::colorize(Cli::bold("[SUCCESS] Completed ".$success." row(s) from ".sizeof($this->_response[$table])." row(s) in `".$table."`"), 'SUCCESS');
		unset($table, $fields);
	}

	/**
	 * Finish action. Show query execution time with request response
	 * @throws Exception
	 */
	public function finishAction()
	{
		// update synx time
		$this->_db->execute("UPDATE `system` SET `value` =	'".time()."' WHERE `key` = 'last_synx'");

		// fixed end queries time
		$time = explode(" ", microtime());
		echo Cli::colorize(sprintf("\n[INFO] Final size length: ".(memory_get_usage()/1024)." kb. \n[INFO] Time elapsed: %f sec.", (($time[1] + $time[0])-$this->_start)), 'WARNING');
	}
}