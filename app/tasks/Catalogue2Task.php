<?php
	use API\APIClient,
		Helpers\Cli;

	/**
	 * Class Catalogue2Task Command Line Client
	 *
	 * Помошники
	 * 	-	Cli::colorize($string, $status);
	 *
	 * @package CLI
	 * @subpackage Tasks
	 */
	class Catalogue2Task extends \Phalcon\CLI\Task
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
					->setURL('http://b.maggadda.dev95.ru/api/jsonrpc/');

				if($this->_api)
					echo Cli::bold(Cli::colorize('API connected success', 'SUCCESS'));

				// get another action
				$this->console->handle([
					'task' 		=> 'catalogue2',
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

			// Get SQL connect
			$this->_db = $this->di->get('db');

			try {

				$system = $this->_db->fetchOne("SELECT * FROM system", Phalcon\Db::FETCH_ASSOC);

				// initialize API client
				$this->_response = $this->_api->call('catalogue.export2', [
					'last_update' 	=> 	(empty($system['value'])) ?
						date("Y-m-d H:i:s", time()-$this->_config->delay) :
						date("Y-m-d H:i:s", $system['value']-$this->_config->delay),
					'start' 		=> 	0,
					'categories' 	=> 	true,
					'limit'			=>	$this->_config->limit,
				]);

				// fixed end queries time
				$time = explode(" ", microtime());

				if(!empty($this->_response['result']))
				{
					echo Cli::colorize('Received a response from the '.Cli::bold('b.maggadda.dev95.ru'.
							sprintf("\nTime: %f sec.", (($time[1] + $time[0])-$this->_start))."\nSize length: ".(memory_get_usage()/1024)." kb.
					"), 'SUCCESS');

					// get another action
					$this->console->handle([
						'task' 		=> 'catalogue2',
						'action' 	=> 'prepare'
					]);
				}
				else
				{
					echo Cli::colorize('Items not found', 'FAILURE');
					// get another action
					$this->console->handle([
						'task' 		=> 'catalogue2',
						'action' 	=> 'finish'
					]);
				}
			}
			catch(Phalcon\Exception $e) {
				echo Cli::colorize($e->getMessage(), 'FAILURE');
			}
		}

		/**
		 * Prepare receiver items for save into DB
		 * @access public
		 */
		public function prepareAction()
		{
			try {

				$this->_response	=	json_decode($this->_response['result'], true);

				try
				{
					if(!empty($this->_response))
					{
						// Get collect data
						$data = [];

						if(isset($this->_response['shops']))
							$data['shops']	=	$this->collectedShops();


						exit;
						// Start transaction
						$this->_db->begin();

						foreach($this->_response as $table => $attributes)
						{
							//if(isset($table))
								//$this->update($table, $attributes);
						}

						// Do the commit
						$this->_db->commit();
					}
					else
						echo Cli::colorize('Nothing to update', 'NOTE');

					// get another action
					$this->console->handle([
						'task' 		=> 'catalogue2',
						'action' 	=> 'finish'
					]);
				}
				catch(\Exception $e)
				{
					$this->_db->rollback();

					// update synx time
					$this->_db->execute("UPDATE `system` SET `value` =	'".time()."' WHERE `key` = 'last_synx'");

					echo Cli::colorize('All update was rollback', 'WARNING');
					echo Cli::colorize($e->getMessage(), 'FAILURE');
					return false;
				}
			}
			catch(Phalcon\Exception $e) {

				// update synx time
				$this->_db->execute("UPDATE `system` SET `value` =	'".time()."' WHERE `key` = 'last_synx'");

				echo Cli::colorize($e->getMessage(), 'FAILURE');
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
			$count = 0;
			foreach($fields as $value)
			{
				$sql	=	"	INSERT INTO ".$table." (".implode(', ',array_keys($value)).")
							VALUES ('".implode("','",array_values($value))."')
							ON DUPLICATE KEY UPDATE ";
				foreach($value as $k => $v)
					$sql .="`$k` = '$v',";

				$sql = rtrim($sql, ',');

				$this->_db->execute($sql);

				++$count;
			}
			echo Cli::bold(Cli::colorize('Updated '.$count.' row(s) from '.sizeof($this->_response[$table]).' row(s) in `'.$table.'`', 'SUCCESS'));
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
			echo Cli::colorize(sprintf("Final size length: ".(memory_get_usage()/1024)." kb.\nTime elapsed: %f sec.", (($time[1] + $time[0])-$this->_start)), 'NOTE');
		}


		private function collectedShops() {

			//var_dump($this->_response['shops']);
			return ;
		}
	}