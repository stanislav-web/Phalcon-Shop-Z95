<?php
namespace Models;

/**
 * Class BuyTogether Модель для `buy_together`
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
	const TABLE = 'buy_together';

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
			$result = $backendCache->get(self::TABLE.'-'.implode('-', $data).'-'.$limit.'.cache');
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
			if($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.implode('-', $data).'-'.$limit.'.cache', $result);
		}
		return $result;
	}

	/**
	 * getProductsIds($product_id, $price_id, $limit = null, $cache = false) Вывод покупаемых товаров в связке к товару
	 * @param      $product_id id товара к которому подбирать покупаемых
	 * @param      $price_id ID ценовой категории магазина
	 * @param null $limit лимит записей
	 * @return \Phalcon\Paginator\Adapter\QueryBuilder
	 */
	public function getProducts($product_id, $price_id, $limit = null, $cache = false)
	{
		$result = null;

		if($cache && $this->_cache)
		{
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.$product_id.'-'.$price_id.'.cache');
		}

		if($result === null)
		{
			// Выполняем запрос из MySQL
			$sql = "";

			$result = $this->_db->query($sql)->fetch();

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.$product_id.'-'.$price_id.'.cache', $result);
		}
		return $result;
	}
}