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
	const PRODUCT_RELATION = 'products_relationship';

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
	 * getProductsForBuy(array $ids, $price_id, $limit = null, $cache = false) Покупаемые товары, передача array ids
	 * @param   array   $ids ID товаров
	 * @param   int		$price_id ценовая категория
	 * @access 	public
	 * @return 	array
	 */
	public function getProductsForBuy(array $ids, $price_id, $limit = null, $cache = false)
	{
		$result = null;

		if($cache && $this->_cache)
		{
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(md5(self::TABLE.$price_id.join('_',$ids).$limit).'.cache');
		}

		if($result === null)
		{
			// Выполняем запрос из MySQL
			$sql = "SELECT  prod.id AS id, prod.articul, prod.name, prod.preview, brand.name AS brand_name, price.price, price.discount
					FROM  ".self::TABLE." prod
					INNER JOIN ".Prices::TABLE." price ON (price.product_id = prod.id)
					INNER JOIN ".Brands::TABLE." brand ON (brand.id = prod.brand_id)
					WHERE prod.id IN(".join(',', $ids).") &&  price.id = ".$price_id;

			if(null != $limit) $sql .= " LIMIT ".$limit;

			$result = $this->_db->query($sql)->fetchAll();

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(md5(self::TABLE.$price_id.join('_',$ids).$limit).'.cache', $result);
		}
		return $result;
	}

	/**
	 * getProducts($price_id, array $condition = array(), $offset = 0, $limit = 10, array $order = [], $cache = false) Вывод товаров в категории с постраничным выводом
	 * @param      $price_id ID цены
	 * @param      $category_id родительская категория
	 * @param null $limit лимит записей
	 * @return \Phalcon\Paginator\Adapter\QueryBuilder
	 */
	public function getProducts($price_id, array $condition = array(), $offset = 0, $limit = 10, array $order = [], $cache = false)
	{
		$result = null;

		if($cache && $this->_cache)
		{
			$backendCache = $this->getDI()->get('backendCache');
			;
			$result = $backendCache->get(md5(self::TABLE.$price_id.join('',$condition).$offset.$limit.join('',$order)).'.cache');
		}

		if($result === null)
		{
		    // Выполняем запрос из MySQL
			$sql = "SELECT SQL_CALC_FOUND_ROWS prod.`id`, prod.`filter_size`,
					prod.`articul` AS articul, prod.`preview` AS preview, prod.name AS name, prod.description as description,
					brand.name AS brand_name, prod.is_new,
					price.price AS price, price.discount AS discount
					FROM `".Common::TABLE_PRODUCTS_REL."` rel
					INNER JOIN `".self::TABLE."` prod ON (prod.id = rel.product_id)
					INNER JOIN `".Prices::TABLE."` price ON (prod.id = price.product_id)
					LEFT JOIN `".Brands::TABLE."` brand ON (brand.id = prod.brand_id)
					INNER JOIN `".Categories::TABLE."` cat ON (cat.id = rel.category_id)
					WHERE price.id = ".$price_id;

			foreach($condition as $key => $value)
			{
				if(sizeof($value) != 1)
					$sql .= " && ".$key." IN(".join(',', $value).") ";
				else
				{
					if(is_array($condition[$key]))
						$sql .= " && ".$key." = ".$condition[$key][0]." ";
					else
					{
						if($condition[$key][0] != '')
							$sql .= " && ".$key." = ".$condition[$key]." ";
					}
				}
			}

			if(!empty($order))
				$sql .= " ORDER BY ".key($order)." ".$order[key($order)];

			if($limit > 0)
				$sql .= " LIMIT ".$offset.",  ".$limit;

			$result = $this->_db->query($sql)->fetchAll();

			$sql = "SELECT FOUND_ROWS() as `count`";
			$found = $this->_db->query($sql)->fetch();
			$result['count'] = $found['count'];

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(md5(self::TABLE.$price_id.join('',$condition).$offset.$limit.join('',$order)).'.cache', $result);
		}
		return $result;
	}

	/**
	 * getTopProducts($price_id, array $condition = array(), $limit = 200, $cache = false) Вывод ТОП товаров с параметрами
	 * @param      $price_id ID цены
	 * @param      $condition условие Where
	 * @param int $limit лимит записей
	 * @return \Phalcon\Paginator\Adapter\QueryBuilder
	 */
	public function getTopProducts($price_id, array $condition = array(), $limit = 200, $cache = false)
	{
		$result = null;

		if($cache && $this->_cache)
		{
			$backendCache = $this->getDI()->get('backendCache');

			$result = $backendCache->get(md5(self::TABLE.$price_id.join('',$condition).$limit).'.cache');
		}

		if($result === null)
		{
			$cond = false;

			foreach($condition as $key => $value)
			{
				if(sizeof($value) != 1)
					$cond .= " && ".$key." IN(".join(',', $value).") ";
				else
				{
					if(is_array($condition[$key]))
						$cond .= " && ".$key." = ".$condition[$key][0]." ";
					else $cond .= " && ".$key." = ".$condition[$key]." ";
				}
			}

			// Выполняем запрос из MySQL
			$sql = "SELECT prod.`id`, prod.`filter_size`,
					prod.`articul` AS articul, prod.`preview` AS preview, prod.name AS name, prod.description as description, prod.is_new,
					brand.name AS brand_name,
					price.price AS price, price.discount AS discount
					FROM `".Common::TABLE_PRODUCTS_REL."` rel
					INNER JOIN `".self::TABLE."` prod ON (prod.id = rel.product_id)
					INNER JOIN `".Prices::TABLE."` price ON (prod.id = price.product_id)
					LEFT JOIN `".Brands::TABLE."` brand ON (brand.id = prod.brand_id)
					INNER JOIN `".Categories::TABLE."` cat ON (cat.id = rel.category_id)
					WHERE price.id = ".$price_id." ".$cond." ORDER BY prod.rating DESC LIMIT ".$limit;

			$result = $this->_db->query($sql)->fetchAll();

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(md5(self::TABLE.$price_id.join('',$condition).$limit).'.cache', $result);
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

	public function getProductCard($articul, $shop_price_id, $cache = false)
	{

		$result = null;
		if($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$shop_price_id.'.cache');
		}
		if($result === null) {
			$sql = "SELECT 	".self::TABLE.".id AS product_id,  CONCAT(UPPER(SUBSTRING(".self::TABLE.".name, 1, 1)), LOWER(SUBSTRING(".self::TABLE.".name FROM 2))) AS product_name, ".self::TABLE.".articul,
					".self::TABLE.".tags,
					".self::TABLE.".rating,
					".self::TABLE.".images,
					".self::TABLE.".filter_size,
					".self::TABLE.".description AS description,"
					.Brands::TABLE.".name AS brand, ".Brands::TABLE.".alias AS brand_alias, ".Prices::TABLE.".price,"
					.Common::TABLE_PRODUCTS_REL.".category_id AS category_id,
					".Prices::TABLE.".discount,
					".Prices::TABLE.".percent,
					".self::TABLE.".description AS description, "
					.Brands::TABLE.".name AS brand, ".Brands::TABLE.".alias AS brand_alias, ".Prices::TABLE.".price

					FROM ".self::TABLE."
					LEFT JOIN ".Prices::TABLE." ON (".Prices::TABLE.".id = $shop_price_id && ".Prices::TABLE.".product_id = ".self::TABLE.".id)
					INNER JOIN ".Common::TABLE_PRODUCTS_REL." ON (".self::TABLE.".id = ".Common::TABLE_PRODUCTS_REL.".product_id)".

					"INNER JOIN ".Brands::TABLE." ON (".self::TABLE.".brand_id = ".Brands::TABLE.".id)
					WHERE ".self::TABLE.".articul = '".$articul."' LIMIT 1";

			$result = $this->_db->query($sql)->fetch();
			if($result) {
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

	public function recountBasketItems($item)
	{
		$id = key($item);
		$items = array();
		foreach($item[$id] as $key => $param){
			list($size, $count) = explode('_', $item[$id][$key]);
			$items[$id]['sizes'][$size] = $count;
		}
		return $items;
	}

	public function getBasketItems($basketItems, $shop_price_id)
	{

		if($basketItems == '' || null === $basketItems) {
			return;
		}
		$ids = implode(',', array_keys($basketItems));

		$sql = "SELECT 	".self::TABLE.".id AS product_id,  ".self::TABLE.".name AS product_name, ".self::TABLE.".articul, ".self::TABLE.".images,
					".Brands::TABLE.".name AS brand, ".Brands::TABLE.".alias AS brand_alias, ".Prices::TABLE.".price,
					".Prices::TABLE.".discount, ".Prices::TABLE.".percent

					FROM ".self::TABLE."
					INNER JOIN ".Prices::TABLE." ON (".Prices::TABLE.".id = $shop_price_id && ".Prices::TABLE.".product_id = ".self::TABLE.".id)
					INNER JOIN ".Brands::TABLE." ON (".self::TABLE.".brand_id = ".Brands::TABLE.".id)
					WHERE product_id IN ($ids) ";

		$result = $this->_db->query($sql)->fetchAll();
	
		if(!empty($result)) {
			//добавляем к вещам информацию о размерах и кол-ве
			$total = 0;
			foreach($result as $key => $item) {
				if(isset($basketItems[$item['product_id']])) {
					$result[$key]['sizes'] = $basketItems[$item['product_id']]['sizes'];
					//считаем общее кол-во вещи одного артикула
					$total = array_sum($basketItems[$item['product_id']]['sizes']);
				}
				$result[$key]['total'] = $total;
				//парсим картинки
				$result[$key]['images'] = json_decode($item['images'], true);
			}
		}

		return $result;
	}

	/**
	 * Возвращает ТОП 10 рекомендованных товаров
	 * @author <filchakov.denis@gmail.com>
	 * @param array $ids
	 */
	public function getRecommend($ids = array(), $limit = 10, $cache = false) {


		$result = null;

		if($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(BuyTogether::TABLE.'-'.implode('-', $ids).'-'.$limit.'.cache');
		}

		if($result === null) {
			$info = $this->_db->query("SELECT top_ten FROM ".BuyTogether::TABLE." WHERE id IN (".implode(',',$ids).')')->fetchAll();
			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $backendCache->save(BuyTogether::TABLE.'-'.implode('-', $ids).'-'.$limit.'.cache', $result);
		}

	}
}