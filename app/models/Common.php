<?php
	namespace Models;

	/**
	 * Class Common Модель для `categories`
	 *
	 * Получает идентификатор соединения $this->_db = $this->getReadConnection();
	 *
	 * @package Shop
	 * @subpackage Models
	 */
	class Common extends \Phalcon\Mvc\Model
	{

		private

			/**
			 * Идентификатор соединения
			 * @var null
			 */
			$_db 	= 	false,

			/**
			 * Статус кэширования
			 * @var boolean
			 */
			$_cache	=	false;

		/**
		 * Инициализация соединения
		 * @return \Phalcon\Db\Adapter\PDO
		 */
		public function initialize()
		{
			if(!$this->_db)
				$this->_db = $this->getReadConnection();

			if(!$this->_cache)
				$this->_cache = $this->getDI()->get('config')->cache->backend;
		}

		/**
		 * Сбор данных из нескольких таблиц (предпочтительно забрать запрос в кэш)
		 *
		 * @param array $tables
		 * @param array $aliases
		 * @param       $cache
		 * @access public
		 * @return null
		 */
		public function getCollectedData(array $tables, $cache = false)
		{
			$result = null;

			if($cache && $this->_cache) {
				$backendCache = $this->getDI()->get('backendCache');
				$result = $backendCache->get(__FUNCTION__.'-'.serialize($tables).'.cache');
			}

			if($result === null)
			{
				$sql = "SELECT ";

				foreach($tables as $table => $fields)
				{
					foreach($fields as $field)
					{
						$sql .= $table.".".$field." AS ".$table."_".$field.",";
					}
				}

				$sql = rtrim($sql, ",");

				$sql .= " FROM ";
				$sql .= implode(", ",array_keys($tables));

				$result = $this->_db->query($sql)->fetchAll();

				// Кеширую если кэш включен
				if($cache && $this->_cache) $backendCache->save(__FUNCTION__.'-'.serialize($tables).'.cache', $result);
			}
			return $result;
		}
	}