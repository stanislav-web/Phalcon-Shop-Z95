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
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE . '-' . implode('-', $data) . '.cache');
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
			if ($cache && $this->_cache) $backendCache->save(self::TABLE . '-' . implode('-', $data) . '.cache', $result);
		}
		return $result;
	}

	/**
	 * Получение всех тегов товаров которые в категории
	 * @param int     $category_id
	 * @param bool $cache
	 * @return null
	 */
	public function getByProductIds($category_id, $cache = false)
	{
		$result = null;

		if ($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.strtolower(__FUNCTION__).'-' .$category_id.'.cache');
		}

		if($result === null) {    // Выполняем запрос из MySQL

			$sql = "SELECT STRAIGHT_JOIN DISTINCT(tag.id) as id, tag.parent_id, tag.name, tag.alias
					FROM ".self::TABLE." tag
					INNER JOIN ".Common::TABLE_PRODUCTS_REL." rel ON tag.id = rel.tag_id
					WHERE product_id IN(SELECT product_id FROM ".Common::TABLE_PRODUCTS_REL." WHERE category_id = ".$category_id.")";

			$result = $this->_db->query($sql)->fetchAll();

			// Сохраняем запрос в кэше
			if ($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.strtolower(__FUNCTION__).'-' .$category_id.'.cache', $result);
		}
		return $result;
	}

}