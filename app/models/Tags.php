<?php
namespace Models;

/**
 * Class Tags Модель для `tags`
 *
 * Получает идентификатор соединения $this->_db = $this->getReadConnection();
 *
 * @package Shop
 * @subpackage Models
 */
class Tags extends \Phalcon\Mvc\Model
{
	/**
	 * Таблица в базе
	 * @const
	 */
	const TABLE = 'tags';

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

		return $this->_db;
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

		if ($cache && $this->_cache) {
			$_cache = $this->getDI()->get('backendCache');
			$result = $_cache->get(self::TABLE . '-' . implode('-', $data) . '.cache');
		}

		if ($result === null) {    // Выполняем запрос из MySQL

			if(!empty($fields))
				$sql = "SELECT " . rtrim(implode(",",$fields), ",") . "
					FROM " . self::TABLE;
			else
				$sql = "SELECT " . self::TABLE. ".*
					FROM " . self::TABLE;

			if (!empty($data)) {
				foreach ($data as $key => $value) {
					if (is_array($value))
						$sql .= " WHERE " . $key . " IN('" . join('\',\'', $value) . "') ";
					else $sql .= " WHERE " . $key . " = '" . $value . "'";
				}
			}

			if (!empty($order)) $sql .= " ORDER BY " . key($order) . " " . $order[key($order)];

			if (null != $limit) $sql .= " LIMIT " . $limit;

			if (null == $limit || $limit > 1) {
				$result = $this->_db->query($sql)->fetchAll();
			} else {
				$result = $this->_db->query($sql)->fetch();
			}

			// Сохраняем запрос в кэше
			if ($cache && $this->_cache) $_cache->save(self::TABLE . '-' . implode('-', $data) . '.cache', $result);
		}
		return $result;
	}

	/**
	 * Получение всех тегов товаров которые в категории
	 * @param int     $category_id
	 * @param bool $cache
	 * @return null
	 */
	public function getTags($category_id, $cache = false)
	{
		$result = null;

		if ($cache && $this->_cache) {
			$_cache = $this->getDI()->get('backendCache');
			$md5 = md5(self::TABLE.'-'.strtolower(__FUNCTION__).'-' .$category_id);
			$result = $_cache->get($md5.'.cache');
		}

		if($result === null)
		{
		    // Выполняем запрос из MySQL

			$sql = "(	SELECT tag.id as id, tag.parent_id, tag.name, tag.alias, COUNT(rel_tags.product_id) AS count_products
						FROM tags tag

						LEFT JOIN 	`".Products::REL."` rel_tags ON (tag.id = rel_tags.tag_id)
						LEFT JOIN 	`".Products::REL."` rel_categories USING (product_id)
						LEFT JOIN   `".Products::TABLE."` prod ON (rel_tags.`product_id` = prod.id)

						WHERE rel_categories.category_id = ".$category_id." && tag.alias != '0' && prod.published = 1
						GROUP BY tag.id
					)
					UNION ALL
					(
						SELECT tags.id as id, tags.parent_id, tags.name, tags.alias, NULL
						FROM `".self::TABLE."` tags
						WHERE tags.parent_id = 0
					) ORDER BY count_products DESC";

			$result = $this->_db->query($sql)->fetchAll();

			// Сохраняем запрос в кэше
			if ($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
		}
		return $result;
	}
}