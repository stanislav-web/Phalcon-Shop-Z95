<?php
namespace Models;

/**
 * Class ShopsPayments Модель для `shops_payments`
 *
 * Получает идентификатор соединения $this->_db = $this->getReadConnection();
 *
 * @package Shop
 * @subpackage Models
 */
class ShopsPayments extends \Phalcon\Mvc\Model
{
	/**
	 * Таблица в базе
	 * @const
	 */
	const TABLE = 'shops_payments';

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
}