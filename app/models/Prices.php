<?php
namespace Models;

/**
 * Class Prices Модель для `prices`
 *
 * Получает идентификатор соединения $this->_db = $this->getReadConnection();
 *
 * @package Shop
 * @subpackage Models
 */
class Prices extends \Phalcon\Mvc\Model
{
	/**
	 * Таблица
	 * @const TABLE
	 */
	const TABLE = 'prices';

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

	/**
	 * Получение подсчета товаров по скидкам
	 * @param $product_id
	 */
	public function countProductsBySales($price_id, array $sex = [], $cache = false)
	{
		$result = null;

		if ($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$price_id.'-'.join('_', $sex).'.cache');
		}

		if($result === null) {    // Выполняем запрос из MySQL
			$sql = "SELECT STRAIGHT_JOIN percent, sex, COUNT(prices.`product_id`) AS `count`
					FROM ".self::TABLE." prices
					INNER JOIN ".Products::TABLE." products ON (products.id = prices.product_id && prices.id = $price_id)
					WHERE products.sex IN (".join(',', $sex).") && prices.percent > 0
					GROUP BY percent, sex;";
			$result = $this->_db->query($sql)->fetchAll();

			// Сохраняем запрос в кэше
			if ($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$price_id.'-'.join('_', $sex).'.cache', $result);
		}
		return $result;
	}
}