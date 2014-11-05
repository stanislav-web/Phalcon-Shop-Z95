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
	 * getProducts($price_id, $category_id, $limit = null, $page = 1, $cache = false) Вывод товаров в категории с постраничным выводом
	 * @param      $price_id ID цены
	 * @param      $category_id родительская категория
	 * @param null $limit лимит записей
	 * @return \Phalcon\Paginator\Adapter\QueryBuilder
	 */
	public function getProducts($price_id, array $condition = array(), $offset = 0, $limit = 10, $cache = false)
	{
		$result = null;

		if($cache && $this->_cache)
		{
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.$price_id.'-'.join('_',$condition).'-'.$offset.'-'.$limit.'.cache');
		}

		if($result === null)
		{
		    // Выполняем запрос из MySQL
			$sql = "SELECT SQL_CALC_FOUND_ROWS prod.`id`,
					prod.`id` AS id, prod.`articul` AS articul, prod.`images` AS images, prod.name AS name, prod.description as description,
					brand.name AS brand_name,
					price.price AS price, price.discount AS discount
					FROM `".Common::TABLE_PRODUCTS_REL."` rel
					INNER JOIN `".self::TABLE."` prod ON (prod.id = rel.product_id)
					INNER JOIN `".Prices::TABLE."` price ON (prod.id = price.product_id)
					INNER JOIN `".Brands::TABLE."` brand ON (brand.id = prod.brand_id)
					WHERE price.id = ".$price_id;

			foreach($condition as $key => $value)
			{
				if(sizeof($value) != 1)
					$sql .= " && ".$key." IN(".join(',', $value).") ";
				else
				{
					if(is_array($condition[$key]))
						$sql .= " && ".$key." = ".$condition[$key][0]." ";
					else $sql .= " && ".$key." = ".$condition[$key]." ";

				}
			}

			$sql .= "LIMIT ".$offset.",  ".$limit;

			$result = $this->_db->query($sql)->fetchAll();

			$sql = "SELECT FOUND_ROWS() as `count`";

			$found = $this->_db->query($sql)->fetch();
			$result['count'] = $found->count;

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.$price_id.'-'.join('_',$condition).'-'.$offset.'-'.$limit.'.cache', $result);
		}
		return $result;
	}

	/**
	 * getProductsIds($price_id, $category_id, $cache = false) Запрос IDS всех товаров в категории
	 * @param      $price_id ID цены
	 * @param      $category_id родительская категория
	 * @param null $limit лимит записей
	 * @return \Phalcon\Paginator\Adapter\QueryBuilder
	 */
	public function getProductsIds($price_id, $category_id, $cache = false)
	{
		$result = null;

		if($cache && $this->_cache)
		{
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.$price_id.'-'.$category_id.'.cache');
		}

		if($result === null)
		{
			// Выполняем запрос из MySQL
			$sql = "SELECT GROUP_CONCAT(CONCAT(rel.product_id)) as prod_ids
					FROM `".Common::TABLE_PRODUCTS_REL."` rel
					INNER JOIN `".Prices::TABLE."` price ON (rel.product_id = price.product_id)
					WHERE price.id = ".$price_id." && rel.category_id = ".$category_id;

			$result = $this->_db->query($sql)->fetch();

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.$price_id.'-'.$category_id.'.cache', $result);
		}
		return $result;
	}





	public function getNewProducts($price_id, $limit = 1, $cache = false)
	{
		$result = null;
		if($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$limit.'.cache');
		}
		if($result === null) {
				$sqlNewProducts = "SELECT ".self::TABLE.".name, ".self::TABLE.".articul, ".self::TABLE.".description, ".Prices::TABLE.".price, brands.name AS brand, brands.alias AS brands_alias
					 FROM ".Products::TABLE."
					 INNER JOIN ".Prices::TABLE." ON ".Products::TABLE.".id = ".Prices::TABLE.".product_id
					 INNER JOIN ".Brands::TABLE." ON ".Products::TABLE.".brand_id = ".Brands::TABLE.".id
					 WHERE ".Products::TABLE.".published = 1
					 AND ".Prices::TABLE.".id = " . $price_id .
				" ORDER BY ".Products::TABLE.".id DESC LIMIT " . $limit;

			$result = $this->_db->query($sqlNewProducts)->fetchAll();

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$limit.'.cache', $result);
		}
		return $result;
	}

	public function getTopProducts($price_id, $limit = 1, $cache = false)
	{
		$result = null;
		if($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$limit.'.cache');
		}
		if($result === null) {
			$sqlNewProducts = "SELECT ".self::TABLE.".name, ".self::TABLE.".description, ".self::TABLE.".articul, ".Prices::TABLE.".price, brands.name AS brand, brands.alias AS brands_alias
					 FROM ".Products::TABLE."
					 INNER JOIN ".Prices::TABLE." ON ".Products::TABLE.".id = ".Prices::TABLE.".product_id
					 INNER JOIN ".Brands::TABLE." ON ".Products::TABLE.".brand_id = ".Brands::TABLE.".id
					 WHERE ".Products::TABLE.".published = 1
					 AND ".Prices::TABLE.".id = " . $price_id .
				" ORDER BY ".Products::TABLE.".rating DESC LIMIT " . $limit;

			$result = $this->_db->query($sqlNewProducts)->fetchAll();

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$limit.'.cache', $result);
		}
		return $result;
	}

	public function getProductCard($articul, $shop_price_id, $cache = false)
	{

		$result = null;
		if($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$shop_price_id.'.cache');
		}
		if($result === null) {
			$sql = "SELECT 	".self::TABLE.".id AS product_id,  ".self::TABLE.".name AS product_name, ".self::TABLE.".articul,
					".self::TABLE.".tags,
					".self::TABLE.".images,
					".self::TABLE.".filter_size,
					".self::TABLE.".description AS description, "
//					GROUP_CONCAT(CONCAT(".Tags::TABLE.".id)) AS all_tags,
//					GROUP_CONCAT(CONCAT(".Tags::TABLE.".name)) AS all_tags_name,
//					GROUP_CONCAT(CONCAT(".Categories::TABLE.".name)) AS category_name,
					.Brands::TABLE.".name AS brand, ".Brands::TABLE.".alias AS brand_alias, ".Prices::TABLE.".price

					FROM ".self::TABLE."
					LEFT JOIN ".Prices::TABLE." ON (".Prices::TABLE.".id = $shop_price_id && ".Prices::TABLE.".product_id = ".self::TABLE.".id)" .
//					INNER JOIN ".Common::TABLE_PRODUCTS_REL." ON (".self::TABLE.".id = ".Common::TABLE_PRODUCTS_REL.".product_id)

//					LEFT JOIN ".Tags::TABLE." ON (".Tags::TABLE.".id = ".Common::TABLE_PRODUCTS_REL.".tag_id)
//					LEFT JOIN ".Categories::TABLE." ON (".Categories::TABLE.".id = ".Common::TABLE_PRODUCTS_REL.".category_id)
					"INNER JOIN ".Brands::TABLE." ON (".self::TABLE.".brand_id = ".Brands::TABLE.".id)
					WHERE ".self::TABLE.".articul = '".$articul."' LIMIT 1";


			$result = $this->_db->query($sql)->fetch();

			foreach($result as $property => $value) {

				if($property == 'all_tags_name') {
					$result[$property] = explode(',', $value);
				}
				if($property == 'category_name') {
					$result[$property] = explode(',', $value);
				}
				if($property == 'tags') {
					$result[$property] = json_decode($value, true);
				}
				if($property == 'images') {
					$result[$property] = json_decode($value, true);
				}
				if($property == 'filter_size') {
					$result[$property] = explode(',', $value);
				}

			}
			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$shop_price_id.'.cache', $result);
		}
		return $result;
	}

	public function getProductsForCart($ids, $shop_price_id, $cart)
		
	{
		if($ids == '' || null === $ids) {
			return;
		}
		$sql = "SELECT 	".self::TABLE.".id AS product_id,  ".self::TABLE.".name AS product_name, ".self::TABLE.".articul,
					".Brands::TABLE.".name AS brand, ".Brands::TABLE.".alias AS brand_alias, ".Prices::TABLE.".price

					FROM ".self::TABLE."
					INNER JOIN ".Prices::TABLE." ON (".Prices::TABLE.".id = $shop_price_id && ".Prices::TABLE.".product_id = ".self::TABLE.".id)
					INNER JOIN ".Brands::TABLE." ON (".self::TABLE.".brand_id = ".Brands::TABLE.".id)
					WHERE product_id IN ($ids) ";

		$result = $this->_db->query($sql)->fetchAll();

		if(!empty($result)) {
			foreach($result as $key => $item) {

				if(isset($cart[$item->product_id])) {

					$result[$key]->quantity_wanted = $cart[$item->product_id]['quantity_wanted'];
					$result[$key]->sizes = $cart[$item->product_id]['sizes'];
				
				}
			}
		}
		return $result;
	}
}