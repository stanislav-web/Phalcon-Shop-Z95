<?php
use Helpers\Cli;

/**
 * Class TagsTask Command Line Client
 *
 * Помошники
 * 	-	Cli::colorize($string, $status);
 *
 * @package CLI
 * @subpackage Tasks
 */
class TagsTask extends \Phalcon\CLI\Task
{
	private
		$_config,
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

			// initialize cli
            $this->_db = $this->di->get('db');
			echo Cli::colorize(Cli::bold("[SUCCESS] Cron start"), 'SUCCESS');

			// get another action
			$this->console->handle([
				'task' 		=> 'tags',
				'action' 	=> 'update'
			]);
		}
		catch(Phalcon\Exception $e) {
			echo Cli::colorize("[FAIL] ".$e->getMessage(), 'FAILURE');
		}
	}

	/**
	 * Update items for save into DB
	 * @access public
	 */
	public function updateAction()
	{
        // Обновление связей Названий товаров (типы) из products -> tags -> products_relationship
        $titlesSQL = "
                INSERT INTO `products_relationship` (product_id, category_id, tag_id)
      SELECT products.id AS product_id, 0 AS category_id,  tag.id AS tag_id
      FROM tags tag
        INNER JOIN products	ON (TRIM(products.`name`) = TRIM(tag.`name`))
      WHERE tag.parent_id = 23640
    ON DUPLICATE KEY UPDATE
      products_relationship.product_id = product_id,
      products_relationship.category_id = category_id,
      products_relationship.tag_id = tag_id";

        $success = 0;

		try
		{
            // Start transaction
            $this->_db->begin();

            $status = $this->_db->execute($titlesSQL);
            if($status)
                ++$success;

            // Do the commit
            $this->_db->commit();

            // get another action
            $this->console->handle([
                'task' 		=> 'tags',
                'action' 	=> 'finish'
            ]);
            echo Cli::colorize(Cli::bold("[SUCCESS] Completed ".$success." row(s)"), 'SUCCESS');
        }
		catch(Phalcon\Exception $e)
		{
            $this->_db->rollback();

            echo Cli::colorize(Cli::bold("[INFO] All updates was rollback by transaction"), 'WARNING');
            echo Cli::colorize("[FAIL] ".$e->getMessage(), 'FAILURE');
            return false;
		}

	}

	/**
	 * Finish action. Show query execution time with request response
	 * @throws Exception
	 */
	public function finishAction()
	{
		// fixed end queries time
		$time = explode(" ", microtime());
		echo Cli::colorize(sprintf("\n[INFO] Final size length: ".(memory_get_usage()/1024)." kb. \n[INFO] Time elapsed: %f sec.", (($time[1] + $time[0])-$this->_start)), 'WARNING');
	}
}