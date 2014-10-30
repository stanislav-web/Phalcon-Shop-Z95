<?php
namespace Models;

/**
 * Class Prices Модель для `prices`
 *
 * @package Shop
 * @subpackage Models
 */
class Prices extends \Phalcon\Mvc\Model
{
	const TABLE = 'prices';
	/**
	 * Инициализация соединения
	 * @return \Phalcon\Db\Adapter\PDO
	 */
	public function initialize()
	{
		if(!$this->db)
			$this->db = $this->getReadConnection();
		return $this->db;
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

		if($cache && $this->getDI()->get('config')->cache->backend) {
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
				$result = $this->db->query($sql)->fetchAll();
			} else {
				$result = $this->db->query($sql)->fetch();
			}

			// Сохраняем запрос в кэше
			if($cache) $backendCache->save(self::TABLE.'-'.serialize($data).'.cache', $result);
		}

		return $result;
	}
}