<?php
namespace Models;

class Categories extends \Phalcon\Mvc\Model
{
	/**
	 * Идентификатор соединений
	 * @var null
	 */
	public $db = false;

	/**
	 * Таблица в базе
	 * @const
	 */
	const TABLE = 'categories';

	/**
	 * Инициализация соединения
	 */
	public function initialize()
	{
		if(!$this->db)
			$this->db = $this->getReadConnection();
	}

	/**
	 * Select data from table
	 * @param array $data pair field=value | empty
	 * @param array $order pair field=sort type | empty
	 * @author Stanislav WEB
	 * @access static
	 * @return null | array
	 */
	public function get(array $data, $order = array(), $limit = null)
	{
		$result = null;

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

		// Вывод результата
		$result = $this->getReadConnection()->query($sql)->fetch();
		return $result;
	}
}