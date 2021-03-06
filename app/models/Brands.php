<?php
namespace Models;

/**
 * Class Brands Модель для `brands`
 *
 * Получает идентификатор соединения $this->_db = $this->getReadConnection();
 *
 * @package Shop
 * @subpackage Models
 */
class Brands extends \Phalcon\Mvc\Model
{
	/**
	 * Таблица в базе
	 * @const
	 */
	const TABLE = 'brands';

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
			$_cache = $this->getDI()->get('backendCache');
			$result = $_cache->get(self::TABLE.'-'.implode('-', $data).'.cache');
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
			if($cache && $this->_cache) $_cache->save(self::TABLE.'-'.implode('-', $data).'.cache', $result);
		}

		return $result;
	}

	/**
	 * Получение списка брендов для товаров в выбранной категории
	 * использую для фильтров
	 *
	 * @param  int    $category_id
	 * @param bool $cache
	 * @return null
	 */
	public function getBrandsByCategory($category_id, $cache = false)
	{
		$result = null;

		if($cache && $this->_cache)
		{
			$_cache = $this->getDI()->get('backendCache');
			$md5 = md5(self::TABLE.$category_id);
			$result = $_cache->get($md5.'.cache');
		}

		if($result === null)
		{
			// Выполняем запрос из MySQL
			$sql = "SELECT brand.id, brand.name, COUNT(brand.id) AS count_products
					FROM `".self::TABLE."` brand
					INNER JOIN `".Products::TABLE."` prod ON (brand.id = prod.brand_id)
					INNER JOIN `".Products::REL."` rel ON (rel.product_id = prod.id)
					WHERE rel.category_id = ".$category_id." && prod.published = 1
					GROUP BY brand.id
					ORDER BY count_products DESC, brand.name";

			$result = $this->_db->query($sql)->fetchAll();

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
		}
		return $result;
	}

	/**
	 * Получение списка брендов с количеством позиций в каждом из них
	 *
	 * @param  int    $price_id магазин
	 * @param bool $cache
	 * @author <filchakov.denis@gmail.com>
	 * @modify Stanislav WEB
	 */
	public function getAllBrands($price_id = 1, $cache = false)
	{
		$result = null;
		if($cache && $this->_cache)
		{
			$_cache = $this->getDI()->get('backendCache');
			$md5 = md5(self::TABLE.$price_id.'-allbrands');
			$result = $_cache->get($md5.'.cache');
		}
		if($result === null)
		{
			$sql = "SELECT b.id, b.name, COUNT(*) AS `count`
					FROM brands b
					INNER JOIN `".Products::TABLE."` product ON (product.brand_id = b.id)
					INNER JOIN `".Prices::TABLE."` price ON (price.product_id = product.id)
					WHERE price.id = ".(int)$price_id." && product.published = 1
					GROUP BY b.id ORDER BY name ASC";

			$result = $this->_db->query($sql)->fetchAll();
			if($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
		}
		return $result;
	}

}