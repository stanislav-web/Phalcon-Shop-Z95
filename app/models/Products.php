<?php
namespace Models;

/**
 * Class Products Модель для `products`
 *
 * Получает идентификатор соединения $this->_db = $this->getReadConnection();
 *
 * @package Shop
 * @subpackage Models
 */
class Products extends \Phalcon\Mvc\Model
{
	/**
	 * Таблица в базе
	 * @const
	 */
	const TABLE = 'products';

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
	 * @param array $data pair field=value | empty          Параметр WHERE
	 * @param array $order pair field=sort type | empty     Сортировка: поле => порядок
	 * @param int $limit 0123... |                          Лимит выборки
	 * @param boolean $cache                                Использовать кэш?
	 * @access public
	 * @return null | array
	 */
	public function get(array $data, $order = [], $limit = null, $cache = false)
	{

		$result = null;

		if($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.serialize($data).'.cache');
		}

		if($result === null) {    // Выполняем запрос из MySQL

			$sql = "SELECT ".self::TABLE.".*
				FROM ".self::TABLE;

			if(!empty($data))
			{
				foreach($data as $key => $value)
				{
					if(is_array($value))
						$sql .= " WHERE ".$key." IN(".join(',', $value)." ";
					else $sql .= " WHERE ".$key." = '".$value."'";
				}
			}

			if(!empty($order)) $sql .= " ORDER BY ".key($order)." ".$order[key($order)];

			if(null != $limit) $sql .= " LIMIT ".$limit;

			if(null != $limit && $limit > 1) {
				$result = $this->_db->query($sql)->fetchAll();
			} else {
				$result = $this->_db->query($sql)->fetch();
			}

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.serialize($data).'.cache', $result);
		}

		return $result;
	}


	public function getNewProducts($price_id, $limit = 1, $cache = false)
	{
		$result = null;
		if($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.__FUNCTION__.'-'.$limit.'.cache');
		}
		if($result === null) {
				$sqlNewProducts = "SELECT ".Products::TABLE.".*, ".Prices::TABLE.".price, brands.name AS brand, brands.alias AS brands_alias
					 FROM ".Products::TABLE."
					 INNER JOIN ".Prices::TABLE." ON ".Products::TABLE.".id = ".Prices::TABLE.".product_id
					 INNER JOIN ".Brands::TABLE." ON ".Products::TABLE.".brand_id = ".Brands::TABLE.".id
					 WHERE ".Products::TABLE.".published = 1
					 AND ".Prices::TABLE.".id = " . $price_id .
				" ORDER BY ".Products::TABLE.".id DESC LIMIT " . $limit;

			$result = $this->_db->query($sqlNewProducts)->fetchAll();

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.__FUNCTION__.'-'.$limit.'.cache', $result);
		}
		return $result;
	}

	public function getTopProducts($price_id, $limit = 1, $cache = false)
	{
		$result = null;
		if($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.__FUNCTION__.'-'.$limit.'.cache');
		}
		if($result === null) {
			$sqlNewProducts = "SELECT ".Products::TABLE.".*, ".Prices::TABLE.".price, brands.name AS brand, brands.alias AS brands_alias
					 FROM ".Products::TABLE."
					 INNER JOIN ".Prices::TABLE." ON ".Products::TABLE.".id = ".Prices::TABLE.".product_id
					 INNER JOIN ".Brands::TABLE." ON ".Products::TABLE.".brand_id = ".Brands::TABLE.".id
					 WHERE ".Products::TABLE.".published = 1
					 AND ".Prices::TABLE.".id = " . $price_id .
				" ORDER BY ".Products::TABLE.".rating DESC LIMIT " . $limit;

			$result = $this->_db->query($sqlNewProducts)->fetchAll();

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.__FUNCTION__.'-'.$limit.'.cache', $result);
		}
		return $result;
	}
}