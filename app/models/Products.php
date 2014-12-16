<?php
namespace Models;
use Phalcon\Db\Column;
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
	const TABLE 	= 	'products';
	const REL		=	'products_relationship';
	const BUY_WITH	=	'buy_together';

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
		$_cache	=	false,

		/**
		 * Ценовая политика
		 * @var int
		 */
		$_price_id	=	1,

		/**
		 * Доступные фильтры для модели по умолчанию
		 * @var array
		 */
		$_filters	=	array(
			'offset'	=>	0,
			'limit'		=>	100,
			'group'		=>	'id',
			'sort'		=>	['rating' => 'desc'],
			'sex'		=>	false,
			'tags'		=>	false,
			'price'		=>	false,
			'categories'=>	false,
			'brands'	=>	false,
			'percent'	=>	false,
			'is_new'	=>	0,
	);

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

		$price_id = $this->getDI()->getSession()->get('price_id');

		if(isset($price_id))
			$this->_price_id = $price_id;
	}

	/**
	 * Установка фильров по умолчанию,
	 * там где offset и limit - идет пересчет на сдвиг для paginate
	 * @param array boolean false
	 * @return null
	 */
	private function setFilters($filters)
	{
		if(!empty($filters))
		{
			if(isset($filters['page']))
			{
				if(!isset($filters['limit']))
					$filters['limit']	=	(int)$this->_filters['limit'];

				if(!isset($filters['offset']))
					$filters['offset']	=	(int)($filters['limit']*$filters['page']);
			}

			foreach($filters as $key => $val)
			{
				if(isset($this->_filters[$key]))
					$this->_filters[$key]	=	$val;
			}
		}
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
	public function get(array $fields = [], array $data, $order = [], $limit = null, $cache = false)
	{

		$result = null;

		if($cache && $this->_cache) {
			$_cache = $this->getDI()->get('backendCache');
			$md5	=	md5(self::TABLE.'-'.implode('-', $data).'-'.$limit);
			$result = $_cache->get($md5.'.cache');
		}

		if($result === null) {    // Выполняем запрос из MySQL

			if(!empty($fields))
				$sql = "SELECT prod.id AS id, " . rtrim(implode(", ",$fields), ", ") . "
					FROM " . self::TABLE." prod";
			else
				$sql = "SELECT prod.*
					FROM " . self::TABLE." prod";

			$sql .= " INNER JOIN `".Prices::TABLE."` price ON (prod.id = price.product_id)
					  LEFT JOIN `".Brands::TABLE."` brand ON (brand.id = prod.brand_id)";

			if(!empty($data))
			{
				$sql .= " WHERE prod.published = 1 AND ";
				$i = 0;
				foreach($data as $key => $value)
				{
					if($i > 0) $sql .= " AND";
					if(is_array($value))
					{
						$sql .= " ".$key." IN(".join(',', $value).") ";
					}
					else $sql .= " ".$key." = '".$value."'";
				 	$i++;
				}
			}

			if(!empty($order)) $sql .= " ORDER BY ".key($order)." ".$order[key($order)];

			if(null != $limit) $sql .= " LIMIT ".$limit;

			if(($limit && $limit > 1) || !isset($limit)) {
				$result = $this->_db->query($sql)->fetchAll();
			} elseif($limit == 1) {

				$result = $this->_db->query($sql)->fetch();
			}

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
		}
		return $result;
	}

	/**
	 * getProductsCategories($condition, $filters, $cache = false) Вывод товаров в категории
	 * @param      $price_id ID цены
	 * @param      $category_id родительская категория
	 * @param null $limit лимит записей
	 * @return \\PDO native array
	 */
	public function getProductsCategories($condition, $filters, $cache = false)
	{
		// фильтрую полученные фильтры
		$this->setFilters($filters);
		$result = null;

		if($cache && $this->_cache)
		{
			$_cache = $this->getDI()->get('backendCache');
			$md5 = md5(self::TABLE.join('',$this->_filters));
			$result = $_cache->get($md5.'.cache');
		}

		if($result === null)
		{
			// Выполняем запрос из MySQL
			$sql = "SELECT SQL_CALC_FOUND_ROWS prod.`id`, prod.`filter_size`,
					prod.`articul` AS articul, prod.`preview` AS preview, prod.name AS name,
					brand.name AS brand_name, prod.is_new,
					price.price AS price, price.discount AS discount
					FROM `".self::REL."` rel
					INNER JOIN `".self::TABLE."` prod ON (prod.id = rel.product_id)
					INNER JOIN `".Prices::TABLE."` price ON (prod.id = price.product_id && discount > 0)
					LEFT JOIN `".Brands::TABLE."` brand ON (brand.id = prod.brand_id)";

			// фильтрация по тегам
			if(!empty($this->_filters['tags'])) {
				$sql .= " LEFT JOIN `" . self::REL . "` tags ON (rel.product_id = tags.product_id)";
			}

			$sql .= " WHERE prod.published = 1 && price.id = ".(int)$this->_price_id." &&";
			if(!empty($condition))
			{
				$i = 0;
				foreach($condition as $key => $value)
				{
					if(sizeof($value) != 1)
						$sql .= " ".$key." IN(".join(',', $value).") ";
					else
					{
						if($i > 0) $sql .= " &&";

						if(is_array($condition[$key]))
							$sql .= " ".$key." ".$condition[$key][0]." ";
						else
						{

							if($condition[$key][0] != '')
								$sql .= " ".$key." ".$condition[$key]." ";
						}
					}
					$i++;
				}
			}

			// фильтрация по тегам
			if(!empty($this->_filters['tags']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " && tags.tag_id IN(".join(',', $this->_filters['tags']).") ";
			}

			// фильтрация по брендам
			if(!empty($this->_filters['brands']))
			{
				$sql = rtrim($sql,'&&');
				if(is_array($this->_filters['brands']))
					$sql .= " && brand.id IN(".join(',', $this->_filters['brands']).") ";
				else $sql .= " && brand.id = ".(int)$this->_filters['brands'];
			}

			// Сортировка
			if(!empty($this->_filters['sort']))
			{
				$sql = rtrim($sql,'&&');
				$sort = implode(', ', array_map(function ($v, $k) { return sprintf("%s %s", $k, $v); }, $this->_filters['sort'], array_keys($this->_filters['sort'])));
				$sql .= " ORDER BY ".$sort;
			}

			// Смещение и лимит
			if(!empty($this->_filters['offset']) && !empty($this->_filters['limit']))
				$sql .= " LIMIT ".(int)$this->_filters['offset'].",  ".(int)$this->_filters['limit'];
			elseif(isset($this->_filters['limit']) > 0)
				$sql .= " LIMIT ".(int)$this->_filters['limit'];

			$result = $this->_db->query($sql)->fetchAll();

			$sql = "SELECT FOUND_ROWS() as `count`";
			$found = $this->_db->query($sql)->fetch();
			$result['count'] = $found['count'];

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
		}
		return $result;
	}

	/**
	 * getProducts($filters, $cache = false) Вывод товаров в категории с постраничным выводом
	 * @param      $price_id ID цены
	 * @param      $category_id родительская категория
	 * @param null $limit лимит записей
	 * @return \PDO native array
	 */
	public function getProducts($filters, $cache = false)
	{
		// фильтрую полученные фильтры
		$this->setFilters($filters);
		$result = null;

		if($cache && $this->_cache)
		{
			$_cache = $this->getDI()->get('backendCache');
			$md5 = md5(self::TABLE.join('',$this->_filters));
			$result = $_cache->get($md5.'.cache');
		}

		if($result === null)
		{
			// Выполняем запрос из MySQL
			$sql = "SELECT SQL_CALC_FOUND_ROWS prod.`id`, prod.`filter_size`,
					prod.`articul` AS articul, prod.`preview` AS preview, prod.name AS name,
					brand.name AS brand_name, prod.is_new,
					price.price AS price, price.discount AS discount
					FROM `".self::REL."` rel
					INNER JOIN `".self::TABLE."` prod ON (prod.id = rel.product_id)
					INNER JOIN `".Prices::TABLE."` price ON (prod.id = price.product_id)
					LEFT JOIN `".Brands::TABLE."` brand ON (brand.id = prod.brand_id)";

			// фильтрация по тегам
			if(!empty($this->_filters['tags'])) {
				$sql .= " LEFT JOIN `" . self::REL . "` tags ON (rel.product_id = tags.product_id)";
			}

			$sql .= " WHERE prod.published = 1 && price.id = ".(int)$this->_price_id." &&";
			if(!empty($condition))
			{
				$i = 0;
				foreach($condition as $key => $value)
				{
					if(sizeof($value) != 1)
						$sql .= " ".$key." IN(".join(',', $value).") ";
					else
					{
						if($i > 0) $sql .= " &&";

						if(is_array($condition[$key]))
							$sql .= " ".$key." ".$condition[$key][0]." ";
						else
						{

							if($condition[$key][0] != '')
								$sql .= " ".$key." ".$condition[$key]." ";
						}
					}
					$i++;
				}
			}

			// выборка из категорий

			if(!empty($this->_filters['categories']))
			{
				$sql = rtrim($sql,'&&');
				if(is_array($this->_filters['categories']))
					$sql .= " && rel.category_id IN(".join(',', $this->_filters['categories']).") ";
				else $sql .= " && rel.category_id = ".(int)$this->_filters['categories'];
			}

			// фильтрация по тегам
			if(!empty($this->_filters['tags']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " && tags.tag_id IN(".join(',', $this->_filters['tags']).") ";
			}

			// фильтрация по брендам
			if(!empty($this->_filters['brands']))
			{
				$sql = rtrim($sql,'&&');
				if(is_array($this->_filters['brands']))
					$sql .= " && brand.id IN(".join(',', $this->_filters['brands']).") ";
				else $sql .= " && brand.id = ".(int)$this->_filters['brands'];
			}

			// фильтрация по полу

			if(!empty($this->_filters['sex']))
			{
				$sql = rtrim($sql,'&&');
				if(is_array($this->_filters['sex']))
					$sql .= " && sex IN(".join(',', $this->_filters['sex']).") ";
				else $sql .= " && sex = ".(int)$this->_filters['sex'];
			}

			// фильтрация по проценту скидки

			if(!empty($this->_filters['percent']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " && percent = ".(int)$this->_filters['percent'];
			}

			// Группировка
			if(!empty($this->_filters['group']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " GROUP BY ".$this->_filters['group'];
			}

			// Сортировка
			if(!empty($this->_filters['sort']))
			{
				$sort = implode(', ', array_map(function ($v, $k) { return sprintf("%s %s", $k, $v); }, $this->_filters['sort'], array_keys($this->_filters['sort'])));
				$sql .= " ORDER BY ".$sort;
			}

			// Смещение и лимит
			if(!empty($this->_filters['offset']) && !empty($this->_filters['limit']))
				$sql .= " LIMIT ".(int)$this->_filters['offset'].",  ".(int)$this->_filters['limit'];
			elseif(isset($this->_filters['limit']) > 0)
				$sql .= " LIMIT ".(int)$this->_filters['limit'];

			$result = $this->_db->query($sql)->fetchAll();

			$sql = "SELECT FOUND_ROWS() as `count`";
			$found = $this->_db->query($sql)->fetch();
			$result['count'] = $found['count'];

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
		}
		return $result;
	}

	/**
	 * getNewProducts($filters, $cache = false) Вывод новинок товаров
	 * @param      $price_id ID цены
	 * @param      $category_id родительская категория
	 * @param null $limit лимит записей
	 * @return \PDO native array
	 */
	public function getNewProducts($filters, $cache = false)
	{
		// фильтрую полученные фильтры
		$this->setFilters($filters);
		$result = null;

		if($cache && $this->_cache)
		{
			$_cache = $this->getDI()->get('backendCache');
			$md5 = md5(self::TABLE.join('',$this->_filters));
			$result = $_cache->get($md5.'.cache');
		}

		if($result === null)
		{
			// Выполняем запрос из MySQL
			$sql = "SELECT SQL_CALC_FOUND_ROWS prod.`id`, prod.`filter_size`,
					prod.`articul` AS articul, prod.`preview` AS preview, prod.name AS name,
					brand.name AS brand_name, prod.is_new,
					price.price AS price, price.discount AS discount
					FROM `".self::REL."` rel
					INNER JOIN `".self::TABLE."` prod ON (prod.id = rel.product_id && prod.is_new = 1)
					INNER JOIN `".Prices::TABLE."` price ON (prod.id = price.product_id)
					LEFT JOIN `".Brands::TABLE."` brand ON (brand.id = prod.brand_id)";

			// фильтрация по тегам
			if(!empty($this->_filters['tags'])) {
				$sql .= " LEFT JOIN `".self::REL."` tags ON (rel.product_id = tags.product_id)";
			}

			$sql .= " WHERE prod.published = 1 && price.id = ".(int)$this->_price_id." &&";
			if(!empty($condition))
			{
				$i = 0;
				foreach($condition as $key => $value)
				{
					if(sizeof($value) != 1)
						$sql .= " ".$key." IN(".join(',', $value).") ";
					else
					{
						if($i > 0) $sql .= " &&";

						if(is_array($condition[$key]))
							$sql .= " ".$key." ".$condition[$key][0]." ";
						else
						{

							if($condition[$key][0] != '')
								$sql .= " ".$key." ".$condition[$key]." ";
						}
					}
					$i++;
				}
			}

			// выборка из категорий

			if(!empty($this->_filters['categories']))
			{
				$sql = rtrim($sql,'&&');
				if(is_array($this->_filters['categories']))
					$sql .= " && rel.category_id IN(".join(',', $this->_filters['categories']).") ";
				else $sql .= " && rel.category_id = ".(int)$this->_filters['categories'];
			}

			// фильтрация по тегам
			if(!empty($this->_filters['tags']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " && tags.tag_id IN(".join(',', $this->_filters['tags']).") ";
			}

			// фильтрация по брендам
			if(!empty($this->_filters['brands']))
			{
				$sql = rtrim($sql,'&&');
				if(is_array($this->_filters['brands']))
					$sql .= " && brand.id IN(".join(',', $this->_filters['brands']).") ";
				else $sql .= " && brand.id = ".(int)$this->_filters['brands'];
			}

			// фильтрация по полу

			if(!empty($this->_filters['sex']))
			{
				$sql = rtrim($sql,'&&');
				if(is_array($this->_filters['sex']))
					$sql .= " && sex IN(".join(',', $this->_filters['sex']).") ";
				else $sql .= " && sex = ".(int)$this->_filters['sex'];
			}

			// фильтрация по проценту скидки

			if(!empty($this->_filters['percent']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " && percent = ".(int)$this->_filters['percent'];
			}

			// Группировка
			if(!empty($this->_filters['group']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " GROUP BY ".$this->_filters['group'];
			}

			// Сортировка
			if(!empty($this->_filters['sort']))
			{
				$sort = implode(', ', array_map(function ($v, $k) { return sprintf("%s %s", $k, $v); }, $this->_filters['sort'], array_keys($this->_filters['sort'])));
				$sql .= " ORDER BY ".$sort;
			}

			// Смещение и лимит
			if(!empty($this->_filters['offset']) && !empty($this->_filters['limit']))
				$sql .= " LIMIT ".(int)$this->_filters['offset'].",  ".(int)$this->_filters['limit'];
			elseif(isset($this->_filters['limit']) > 0)
				$sql .= " LIMIT ".(int)$this->_filters['limit'];

			$result = $this->_db->query($sql)->fetchAll();

			$sql = "SELECT FOUND_ROWS() as `count`";
			$found = $this->_db->query($sql)->fetch();
			$result['count'] = $found['count'];

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
		}
		return $result;
	}

	/**
	 * getProductsDiscount($filters, $cache = false) Вывод товаров для скидок, (оптимальный запрос)
	 * @param      $price_id ID цены
	 * @param      $category_id родительская категория
	 * @param null $limit лимит записей
	 * @return \PDO native array
	 */
	public function getProductsDiscount($filters, $cache = false)
	{
		// фильтрую полученные фильтры
		$this->setFilters($filters);

		$result = null;

		if($cache && $this->_cache)
		{
			$_cache = $this->getDI()->get('backendCache');
			$md5 = md5(self::TABLE.join('',$this->_filters));
			$result = $_cache->get($md5.'.cache');
		}

		if($result === null)
		{
		    // Выполняем запрос из MySQL
			$sql = "SELECT SQL_CALC_FOUND_ROWS prod.`id`, prod.`filter_size`,
					prod.`articul` AS articul, prod.`preview` AS preview, prod.name AS name,
					brand.name AS brand_name, prod.is_new,
					price.price AS price, price.discount AS discount
					FROM `prices` price
					INNER JOIN `".self::TABLE."` prod   ON (prod.id = price.product_id && (discount > 0 && price.id = ".(int)$this->_price_id."))
					LEFT JOIN `brands` brand ON (brand.id = prod.brand_id)";

			// выборка из категорий

			if(!empty($this->_filters['categories']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " INNER JOIN `products_relationship` rel ON (rel.product_id = prod.id)";
				$sql .= " && rel.category_id IN(".join(',', $this->_filters['categories']).") ";
			}

			$sql .="WHERE prod.published = 1";

			// фильтрация по тегам
			if(!empty($this->_filters['tags']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " && tags.tag_id IN(".join(',', $this->_filters['tags']).") ";
			}

			// фильтрация по брендам
			if(!empty($this->_filters['brands']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " && brand.id IN(".join(',', $this->_filters['brands']).") ";
			}

			// фильтрация по полу

			if(!empty($this->_filters['sex']))
			{
				$sql = rtrim($sql,'&&');
				if(is_array($this->_filters['sex']))
					$sql .= " && sex IN(".join(',', $this->_filters['sex']).") ";
				else $sql .= " && sex = ".(int)$this->_filters['sex'];
			}

			// фильтрация по проценту скидки

			if(!empty($this->_filters['percent']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " && percent = ".(int)$this->_filters['percent'];
			}
			else
				$sql .= " && percent > 0";

			// фильтрация по новизне

			if(!empty($this->_filters['is_new']))
			{
				$sql = rtrim($sql,'&&');
				$sql .= " && is_new = ".(int)$this->_filters['is_new'];
			}

			// Сортировка
			if(!empty($this->_filters['sort']))
			{
				$sort = implode(', ', array_map(function ($v, $k) { return sprintf("%s %s", $k, $v); }, $this->_filters['sort'], array_keys($this->_filters['sort'])));
				$sql .= " ORDER BY ".$sort;
			}

			// Смещение и лимит
			if(!empty($this->_filters['offset']) && !empty($this->_filters['limit']))
				$sql .= " LIMIT ".(int)$this->_filters['offset'].",  ".(int)$this->_filters['limit'];
			elseif(isset($this->_filters['limit']) > 0)
				$sql .= " LIMIT ".(int)$this->_filters['limit'];

			$result = $this->_db->query($sql)->fetchAll();

			$sql = "SELECT FOUND_ROWS() as `count`";
			$found = $this->_db->query($sql)->fetch();
			$result['count'] = $found['count'];

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
		}
		return $result;
	}

	/**
	 * getTopProducts($filters, $limit = 200, $cache = false) Вывод ТОП товаров с параметрами (оптимальный запрос)
	 * @param      $price_id ID цены
	 * @param      $condition условие Where
	 * @param int $limit лимит записей
	 * @return \PDO native array
	 */
	public function getTopProducts($filters, $limit = 200, $cache = false)
	{
		// фильтрую полученные фильтры
		$this->setFilters($filters);
		$result = null;

		if($cache && $this->_cache)
		{
			$_cache = $this->getDI()->get('backendCache');
			$md5 = md5(self::TABLE.join('',$filters).$limit);
			$result = $_cache->get($md5.'.cache');
		}

		if($result === null)
		{
			// Выполняем запрос из MySQL
			$sql = "SELECT prod.`id`, prod.`filter_size`,
					prod.`articul` AS articul, prod.`preview` AS preview, prod.name AS name, prod.is_new,
					brand.name AS brand_name,
					price.price AS price, price.discount AS discount
					FROM `".self::TABLE."` prod
					INNER JOIN `".Prices::TABLE."` price ON (prod.id = price.product_id)
					LEFT JOIN `".Brands::TABLE."` brand ON (brand.id = prod.brand_id)";

			// выборка из категорий
			if(!empty($this->_filters['categories']))
				$sql .= " INNER JOIN `".self::REL."` category ON (
					category.product_id = prod.id && category.category_id IN(".join(',', $this->_filters['categories']).")
				)";

				$sql .= " WHERE prod.published = 1 && price.id= ".(int)$this->_price_id;

			// фильтрация по полу

			if(!empty($this->_filters['sex'])) {
				$sql = rtrim($sql,'&&');
				if(is_array($this->_filters['sex']))
					$sql .= " && sex IN(".join(',', $this->_filters['sex']).") ";
				else
					$sql .= " && sex = ".(int)$this->_filters['sex'];
			}

			// Сортировка
			if(!empty($this->_filters['sort']))
			{
				$sort = implode(', ', array_map(function ($v, $k) { return sprintf("%s %s", $k, $v); }, $this->_filters['sort'], array_keys($this->_filters['sort'])));
				$sql .= " ORDER BY ".$sort;
			}

			$sql .= " LIMIT ".(int)$limit;

			$result = $this->_db->query($sql)->fetchAll();

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
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
			$_cache = $this->getDI()->get('backendCache');
			$result = $_cache->get(md5(self::TABLE.$price_id.join('_',$ids).$limit).'.cache');
		}

		if($result === null)
		{
			// Выполняем запрос из MySQL
			$sql = "SELECT  prod.id AS id, prod.articul, prod.name, prod.preview, brand.name AS brand_name, price.price, price.discount
					FROM  ".self::TABLE." prod
					INNER JOIN ".Prices::TABLE." price ON (price.product_id = prod.id)
					INNER JOIN ".Brands::TABLE." brand ON (brand.id = prod.brand_id)
					WHERE prod.id IN(".join(',', $ids).") && prod.published = 1 &&  price.id = ".(int)$price_id;

			if(null != $limit) $sql .= " LIMIT ".$limit;

			$result = $this->_db->query($sql)->fetchAll();

			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $_cache->save(md5(self::TABLE.$price_id.join('_',$ids).$limit).'.cache', $result);
		}
		return $result;
	}

	/**
	 * getProductCard($articul, $shop_price_id, $cache = false)
	 * Загрузка карточки товара
	 *
	 * @param      $articul
	 * @param      $shop_price_id
	 * @param bool $cache
	 * @author vavas
	 * @modify Stanislav WEB
	 * @return null
	 */
	public function getProductCard($articul, $shop_price_id, $cache = false)
	{
		$result = null;
		if($cache && $this->_cache)
		{
			$_cache = $this->getDI()->get('backendCache');
			$md5 = md5(self::TABLE.'-'.strtolower(__FUNCTION__).$articul);
			$result = $_cache->get($md5.'.cache');
		}
		if($result === null)
		{
			$sql = "SELECT 	prod.id AS product_id,  CONCAT(UPPER(SUBSTRING(prod.name, 1, 1)), LOWER(SUBSTRING(prod.name FROM 2))) AS product_name,
						prod.articul, prod.tags, prod.rating, prod.images, prod.filter_size, prod.description AS description,
						brand.name AS brand,
						price.price, price.discount, price.percent,
						prod_rel.category_id AS category_id
						FROM ".self::TABLE." prod
						LEFT JOIN ".Prices::TABLE." price ON (price.id = ".(int)$shop_price_id." && price.product_id = prod.id)
						INNER JOIN ".self::REL." prod_rel ON (prod.id = prod_rel.product_id)
						LEFT JOIN ".Brands::TABLE." brand ON (prod.brand_id = brand.id)
						WHERE prod.articul = ".(int)$articul." && prod.published = 1 LIMIT 1";

			$result = $this->_db->query($sql)->fetch();
			if($result)
			{
				foreach($result as $property => $value)
				{
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
						if(!empty($value))
							$result[$property] = json_decode($value, true);
					}
				}
			}
			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
		}
		return $result;
	}

	/**
	 *  getRecommend($ids = array(), $limit = 10, $cache = false) Возвращает ТОП 10 рекомендованных товаров
	 * @param       array ids список id товаров
	 * @author <filchakov.denis@gmail.com>
	 * @modify Stanislav WEB
	 * @param array $ids
	 * @return array
	 */
	public function getRecommend(array $ids, $cache = false)
	{
		$result = null;

		if($cache && $this->_cache)
		{
			$_cache = $this->getDI()->get('backendCache');
			$md5 = md5(self::BUY_WITH.'-'.implode('-', $ids));
			$result = $_cache->get($md5.'.cache');
		}

		if($result === null)
		{
			$sql = "SELECT top_ten
						FROM `".self::BUY_WITH."`
						WHERE id";

			if(sizeof($ids) > 1)
			{
				$sql .= "IN (".implode(',',$ids).")";
				$result = $this->_db->query($sql)->fetchAll();
			}
			else
			{
				$sql .= " = ".array_values($ids)[0];
				$result = $this->_db->query($sql)->fetch();
			}
			// Сохраняем запрос в кэше
			if($cache && $this->_cache) $_cache->save($md5.'.cache', $result);
			return $result;
		}
	}
}