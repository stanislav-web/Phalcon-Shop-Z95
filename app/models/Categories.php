<?php
	namespace Models;
	/**
	 * Class Categories Модель для `categories`
	 *
	 * Получает идентификатор соединения $this->_db = $this->getReadConnection();
	 *
	 * @package Shop
	 * @subpackage Models
	 */
	class Categories extends \Phalcon\Mvc\Model
	{
		/**
		 * Таблица в базе
		 * @const
		 */
		const TABLE = 'categories';
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
		 * Получение данных из таблицы
		 * @param array $fields pair fields | empty          	Параметр SELECT
		 * @param array $data pair field=value | empty          Параметр WHERE
		 * @param array $order pair field=sort type | empty     Сортировка: поле => порядок
		 * @param int $limit 0123... |                          Лимит выборки
		 * @param boolean $cache                                Использовать кэш?
		 * @access public
		 * @return null | array
		 */
		public function get(array $fields = [], array $data = [], $order = [], $limit = null, $cache = false)
		{
			$result = null;
			if($cache && $this->_cache)
			{
				$backendCache = $this->getDI()->get('backendCache');
				$md5 = md5(self::TABLE.implode('-', $fields).implode('-', $data));
				$result = $backendCache->get($md5. '.cache');
			}

			if($result === null) // Выполняем запрос из MySQL
			{
				if(!empty($fields))
					$sql = "SELECT " . rtrim(implode(",",$fields), ",") . "
					FROM " . self::TABLE;
				else
					$sql = "SELECT " . self::TABLE. ".*
					FROM " . self::TABLE;
				if(!empty($data))
				{
					foreach($data as $key => $value)
					{
						if(is_array($value))
							$sql .= " WHERE " . $key . " IN(" . join(',', $value) . ") ";
						else $sql .= " WHERE " . $key . " = '" . $value . "'";
					}
				}
				if(!empty($order)) $sql .= " ORDER BY " . key($order) . " " . $order[key($order)];
				if(null != $limit) $sql .= " LIMIT " . $limit;
				if(null != $limit && $limit > 1) {
					$result = $this->_db->query($sql)->fetchAll();
				}
				else
					$result = $this->_db->query($sql)->fetch();

				// Сохраняем запрос в кэше
				if ($cache && $this->_cache) $backendCache->save($md5.'.cache', $result);
			}
			return $result;
		}

		/**
		 * getCategories($shop_id, $sort, $cache) Получение категорий/подкатегорий выбранного магазина
		 * с изображением самого рейтингового товара в каждой категории
		 *
		 * @param int $shop_id ID магазина
		 * @param int $parent_id ID категории родителя
		 * @param string $conditional мат. выражение !=, >, <, == ...
		 * @param string $sort ASC DESC
		 * @param $cache
		 * @return array
		 */
		public function getCategories($shop_id, $parent_id , $conditional, $sort, $cache)
		{
			$result = null;
			if($cache && $this->_cache)
			{
				$backendCache = $this->getDI()->get('backendCache');
				$md5 = md5(self::TABLE.$shop_id.$parent_id.$sort);
				$result = $backendCache->get($md5.'.cache');
			}

			if($result === null) // Выполняем запрос из MySQL
			{
				$sql =	"SELECT STRAIGHT_JOIN shop_rel.category_id AS id, cat.name AS name,
						(
							SELECT CONCAT('{\"', p.id, '\":', p.images, '}') FROM ".Products::TABLE." p
							INNER JOIN ".Common::TABLE_PRODUCTS_REL." pr ON (pr.product_id = p.id)
							WHERE pr.category_id = shop_rel.`category_id` ORDER BY rating DESC LIMIT 1
						) AS img,
						(
							SELECT alias FROM ".Categories::TABLE." c
							WHERE c.id = cat.parent_id
						) AS parent_alias, cat.alias AS alias, cat.parent_id AS parent_id,
						COUNT(*) AS count_prod, shop_rel.sort AS sort
						FROM ".Common::TABLE_CAT_SHOP_REL." shop_rel
						INNER JOIN ".self::TABLE." cat ON (shop_rel.category_id = cat.id)
						INNER JOIN ".Common::TABLE_PRODUCTS_REL." prod_rel ON (prod_rel.category_id = cat.id)
						INNER JOIN ".Products::TABLE." prod ON (prod.id = prod_rel.product_id)
						WHERE shop_rel.shop_id = ".$shop_id." && shop_rel.category_parent_id ".$conditional." ".$parent_id."
						GROUP BY id
						ORDER BY shop_rel.sort ".$sort;

				$result = $this->_db->query($sql)->fetchAll();

				// Сохраняем запрос в кэше
				if($cache && $this->_cache) $backendCache->save($md5.'.cache', $result);
			}
			return $result;
		}
	}