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
	 * @param array $fields pair fields | empty          	Параметр SELECT
	 * @param array $data pair field=value | empty          Параметр WHERE
	 * @param array $order pair field=sort type | empty     Сортировка: поле => порядок
	 * @param int $limit 0123... |                          Лимит выборки
	 * @param boolean $cache                                Использовать кэш?
	 * @access public
	 * @return null | array
	 */
	public function get(array $fields = [], array $data = [], $order = [], $limit = false, $cache = false)
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
					else $sql .= " AND " . $key . " = '" . $value . "'";
				}
			}
			if(!empty($order)) $sql .= " ORDER BY " . key($order) . " " . $order[key($order)];
			if($limit !=false) $sql .= " LIMIT " . $limit;

			if($limit && $limit > 1)
				$result = $this->_db->query($sql)->fetchAll();
			elseif($limit == false)
				$result = $this->_db->query($sql)->fetchAll();
			else
				$result = $this->_db->query($sql)->fetch();

			// Сохраняем запрос в кэше
			if ($cache && $this->_cache) $backendCache->save($md5.'.cache', $result);
		}
		return $result;
	}

	/**
	 * Получение подсчета товаров по скидкам
	 * @param int $price_id идентификатор цены (из магазина)
	 * @param array $sex нужный пол
	 * @return array
	 */
	public function countProductsBySales($price_id, array $sex = [], $cache = false)
	{
		$result = null;

		if ($cache && $this->_cache) {
			$_cache = $this->getDI()->get('backendCache');
			$md5 = md5(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$price_id.'-'.join('_', $sex));
			$result = $_cache->get($md5.'.cache');
		}

		if($result === null)
		{
			$sql =	"
				SELECT STRAIGHT_JOIN prod.sex AS sex, COALESCE(percent, '100') AS percent, COUNT(prices.`product_id`) AS `count`
					FROM ".self::TABLE." prices
					INNER JOIN ".Products::TABLE." prod ON (prod.id = prices.product_id && prices.id = ".(int)$price_id.")
					WHERE prod.sex IN (".join(',', $sex).") && prices.percent > 0
					GROUP BY sex, percent ASC WITH ROLLUP;
			";

			$result = $this->_db->query($sql)->fetchAll();

			// Сохраняем запрос в кэше
			if ($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
		}
		return $result;
	}
}