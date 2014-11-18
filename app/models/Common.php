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

		/**
		 * Таблицы
		 * @const TABLE_PRODUCTS_REL
		 * @const TABLE_CAT_SHOP_REL
		 */
		const TABLE_PRODUCTS_REL = 'products_relationship';
		const TABLE_CAT_SHOP_REL = 'category_shop_relationship';

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
		 	 * Статус кэширования
		 	 * @var boolean
		 	 */
			public

				/**
				 * Статус кэширования
				 * @var boolean
				 */
				$result	=	null;

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
		 * @example <code>
		 *          	$this->_commonDataTables[]	= 	$this->commonModel->getCollectedData([
		 *					Models\Categories::TABLE	=>	['id', 'name', 'parent_id', 'alias'],
		 *					Models\Tags::TABLE			=>	['id', 'name', 'parent_id', 'alias'],
		 *					Models\Brands::TABLE		=>	['id', 'name', 'alias'],
		 *				]);
		 *          </code>
		 * @param array $tables
		 * @param array $aliases
		 * @param       $cache
		 * @access public
		 * @return null
		 */
		public function getCollectedData(array $tables, $cache = false)
		{
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

				$this->result = $this->_db->query($sql)->fetchAll();

				// Кеширую если кэш включен
				if($cache && $this->_cache) $backendCache->save(__FUNCTION__.'-'.serialize($tables).'.cache', $result);
			}
			return $result;
		}

		/**
		 * Получение категорий с дочерними по конкретному магазину
		 *
		 * @param      $shop_id
		 * @param bool $cache
		 * @return null
		 */
		public function getShopCategories($shop_id, $cache = false)
		{
			$result = null;
			if($cache && $this->_cache) {
				$backendCache = $this->getDI()->get('backendCache');
				$result = $backendCache->get(strtolower(__FUNCTION__).'-'.$shop_id.'.cache');
			}

			if($result === null)
			{

				$sql = "SELECT cat.id, cat.parent_id, sex, cat.name AS name, cat.alias AS alias, shop_rel.sort, COALESCE(COUNT(prod_rel.`product_id`) ,0) AS count_products
						FROM category_shop_relationship shop_rel
						INNER JOIN categories cat ON (
							shop_rel.category_id = cat.id
							-- && shop_rel.category_parent_id = cat.parent_id
						)
						LEFT JOIN `".self::TABLE_PRODUCTS_REL."` prod_rel  USING(category_id)
						WHERE shop_rel.shop_id = ".$shop_id."
						GROUP BY shop_rel.category_id";

				//exit($sql);
				$result = $this->_db->query($sql)->fetchAll();

				// Сохраняем запрос в кэше
				if($cache && $this->_cache) $backendCache->save(strtolower(__FUNCTION__).'-'.$shop_id.'.cache', $result);
			}
			return $result;
		}
	}