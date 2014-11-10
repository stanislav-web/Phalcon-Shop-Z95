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
				$sql = "SELECT 	".Categories::TABLE.".id AS id, ".Categories::TABLE.".parent_id AS parent_id,
							".Categories::TABLE.".name AS name, ".Categories::TABLE.".alias AS alias, ".self::TABLE_CAT_SHOP_REL.".sort AS sort,
							".Categories::TABLE.".description AS description
							FROM  ".self::TABLE_CAT_SHOP_REL."
							INNER JOIN ".Categories::TABLE."
							ON (
									".self::TABLE_CAT_SHOP_REL.".category_id = ".Categories::TABLE.".id
									&& ".self::TABLE_CAT_SHOP_REL.".category_parent_id = ".Categories::TABLE.".parent_id
								)
							WHERE ".self::TABLE_CAT_SHOP_REL.".shop_id = ".$shop_id;


				$result = $this->_db->query($sql)->fetchAll();

				// Сохраняем запрос в кэше
				if($cache && $this->_cache) $backendCache->save(strtolower(__FUNCTION__).'-'.$shop_id.'.cache', $result);
			}
			return $result;
		}


		/**
		 * Подсчет товаров в каждой из выбранных категорий
		 *
		 * @param mixed $categories_ids array(1,56,79,120) или int 56
		 * @param bool 	$cache
		 * @return null
		 */
		public function getCountProducts($categories_ids, $cache = false)
		{
			$result = null;
			if ($cache && $this->_cache) {
				$backendCache = $this->getDI()->get('backendCache');
				$result = $backendCache->get(strtolower(__FUNCTION__) . '-'.implode('_', $categories_ids).'.cache');
			}

			if ($result === null) {

				$sql = "SELECT rel.category_id AS id, COUNT(rel.product_id) AS product_count
						FROM `".self::TABLE_PRODUCTS_REL."` rel ";

						if(is_array($categories_ids))
							$sql .= "WHERE rel.category_id IN(".implode(',', $categories_ids).")";
						else $sql .= "WHERE rel.category_id = ".$categories_ids;
						$sql .= " GROUP BY rel.category_id";

				$result = $this->_db->query($sql)->fetchAll();

				// Сохраняем запрос в кэше
				if ($cache && $this->_cache) $backendCache->save(strtolower(__FUNCTION__) . '-' . implode('_', $categories_ids) . '.cache', $result);
			}
			return $result;
		}

		public function categoriesToTree($array, $parent_id = 0) {
			$tree = array();

			if( !empty($array)){
				foreach( $array as $id=> $element ){

					$element = (array) $element;
					if( !isset($element['parent_id']) ) continue;
					if( $element['parent_id'] == $parent_id ){
						$tree[$id] = $element;
						unset($array[$element['id']]);
						$tree[$id]['childs'] = $this->categoriesToTree($array, $element['id']);
					}
				}
				return $tree;
			}

		}

	}