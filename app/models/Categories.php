<?php
namespace Models;

/**
 * Class Categories Модель для `categories`
 *
 * Получает идентификатор соединения $this->_db = $this->getReadConnection();
 *
 * @package Shop
 * @subpackage Models
 */
class Categories extends \Phalcon\Mvc\Model
{
	/**
	 * Таблица в базе
	 * @const
	 */
	const TABLE = 'categories';

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
	public function get(array $fields = [], array $data = [], $order = [], $limit = null, $cache = false)
	{
		$result = null;

		if ($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE . '-' . implode('-', $data) . '.cache');
		}

		if ($result === null) {    // Выполняем запрос из MySQL

			if(!empty($fields))
				$sql = "SELECT " . rtrim(implode(",",$fields), ",") . "
					FROM " . self::TABLE;
			else
				$sql = "SELECT " . self::TABLE. ".*
					FROM " . self::TABLE;

			if (!empty($data)) {
				foreach ($data as $key => $value) {
					if (is_array($value))
						$sql .= " WHERE " . $key . " IN(" . join(',', $value) . " ";
					else $sql .= " WHERE " . $key . " = '" . $value . "'";
				}
			}

			if (!empty($order)) $sql .= " ORDER BY " . key($order) . " " . $order[key($order)];

			if (null != $limit) $sql .= " LIMIT " . $limit;

			if (null != $limit && $limit > 1) {
				$result = $this->_db->query($sql)->fetchAll();
			} else {
				$result = $this->_db->query($sql)->fetch();
			}

			// Сохраняем запрос в кэше
			if ($cache && $this->_cache) $backendCache->save(self::TABLE . '-' . implode('-', $data) . '.cache', $result);
		}
		return $result;
	}

	/**
	 * Вывод листинга всех категорий
	 * @author <filchakov.denis@gmail.com>
	man/winter-fall	Куртки и пуховики
	accessories/baseball-hats	Кепки
	man/jeans	Джинсы и штаны
	 */
	public function getListing($shopID = 1, $cache = false){
		$result = null;
		if ($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE . '-listing.cache');
		}
		if ($result === null) {
			$sql = "SELECT concat((SELECT alias FROM ".self::TABLE." WHERE id = csr.category_parent_id), '/', (SELECT alias FROM ".self::TABLE." WHERE id = csr.category_id)) as 'url',
				(SELECT name FROM ".self::TABLE." WHERE id = csr.category_id) as 'name',
				csr.category_id
				FROM ".Common::TABLE_CAT_SHOP_REL." csr
				WHERE shop_id = ".$shopID."
				HAVING
				url != ''";
			$result = $this->_db->query($sql)->fetchAll();
			// Сохраняем запрос в кэше
			if ($cache && $this->_cache) $backendCache->save(self::TABLE . '-listing.cache', $result);
		}
		return $result;
	}

	/**
	 * Формирование массива параметров для фильтрации
	 * @author <filchakov.denis@gmail.com>
	 */
	public function parseRemap($shopID = 1, $url = false, $limit = 100, $cache = false){
		$result = null;
		if ($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE . '-'.$url.'.cache');
		}
		if ($result === null) {
			if($url != false){
				$get = $url;
				$url = $url['_url'];
				unset($get['_url']);
				$url = preg_replace("#/$#", "", $url);
				$url = str_replace('/catalogue/','',$url);
				$url = explode('/',$url);
				if(in_array('top',$url)){
					$result['top'] = true;
				}
				if(in_array('new',$url)){
					$result['new'] = true;
				}
				//Забираем категории
				$sql = "SELECT c.id
					FROM ".Common::TABLE_CAT_SHOP_REL." csr
					INNER JOIN categories c ON c.id = csr.category_id
					WHERE csr.shop_id = ".$shopID." AND
					BINARY alias IN ('".implode("','",$url)."')";
				$categoryResult = $this->_db->query($sql)->fetchAll();
				if(count($categoryResult)>0){
					foreach($categoryResult as $categoryInfo){
						$result['category'][] = (int) $categoryInfo['id'];
					}
				}
				//Определяем теги и размеры
				$sql = "SELECT t.alias, t.parent_id, t.id
					FROM tags t
					WHERE alias IN ('".implode("','",$url)."') AND alias != ''";
				$tagsResult = $this->_db->query($sql)->fetchAll();
				if(count($tagsResult)>0){
					foreach($tagsResult as $tagsInfo){
						if(is_numeric($tagsInfo['parent_id'])){
							$result['tags'][] = (int) $tagsInfo['id'];
						} else {
							$result['sizes'][] = (int)$tagsInfo['id'];
						}
					}
				}
				//Определяем бренды
				$sql = "SELECT b.alias, b.id
					FROM brands b
					WHERE BINARY name IN ('".implode("','",$url)."') AND name != ''";
				$tagsResult = $this->_db->query($sql)->fetchAll();
				if(count($tagsResult)>0){
					foreach($tagsResult as $brandInfo){
						$result['brand'][] = (int) $brandInfo['id'];
					}
				}
				if(isset($_REQUEST['sale']) && is_numeric($_REQUEST['sale'])){
					$result['sale'] = (int)$_REQUEST['sale'];
				}
				if(in_array('man',$url)){
					$result['sex'] = 1;
				}
				if(in_array('woman',$url)){
					$result['sex'] = 2;
				}
				//Добавляем параметры по умоланию
				if(isset($_GET['page']) && is_numeric($_GET['page'])){
					$result['page'] = (int) $_GET['page']-1;
				} else {
					$result['page'] = 0;
				}
				if(isset($_GET['limit']) && is_numeric($_GET['limit'])){
					$result['limit'] = (int) $_GET['limit'];
				} else {
					$result['limit'] = $limit;
				}
				$result['sortby'] = 'rating';
				if(isset($_GET['sortby'])){
					switch($_GET['sortby']){
						case "new": $result['sortby'] = 'new'; break;
						case "price": $result['sortby'] = 'price'; break;
						case "discount": $result['sortby'] = 'discount'; break;
						default: case "rating": $result['sortby'] = 'rating'; break;
					}
				}
				$result['orderby'] = 'desc';
				if(isset($_GET['orderby'])){
					switch($_GET['orderby']){
						case "asc": $result['orderby'] = 'asc'; break;
						default: case "desc": $result['orderby'] = 'desc'; break;
					}
				}
				if(isset($_GET['price']['min']) && is_numeric($_GET['price']['min'])){
					$result['price']['min'] = (int) $_GET['price']['min'];
				}
				if(isset($_GET['price']['max']) && is_numeric($_GET['price']['max'])){
					$result['price']['max'] = (int) $_GET['price']['max'];
				}
			}
			if(isset($result['category'][array_search(1,$result['category'])])){
				unset($result['category'][array_search(1,$result['category'])]);
			}
			if(isset($result['category'][array_search(2,$result['category'])])){
				unset($result['category'][array_search(2,$result['category'])]);
			}
			if(isset($result['category']) && count($result['category'])==0){
				unset($result['category']);
			}
			if ($cache && $this->_cache) $backendCache->save(self::TABLE . '-'.$url.'.cache', $result);
		}
		return $result;
	}



	/**
	 * getSubcategories($shop_id, $sort, $cache) Получение подкатегорий выбранного магазина
	 * с изображением самого рейтингового товара в каждой категории
	 *
	 * @param int $shop_id ID магазина
	 * @param int $parent_id ID категории родителя
	 * @param string $conditional мат. выражение !=, >, <, == ...
	 * @param string $sort ASC DESC
	 * @param $cache
	 * @return array
	 */
	public function getCategories($shop_id, $parent_id , $conditional, $sort, $cache)
	{
		$result = null;

		if ($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$shop_id.'.cache');
		}

		if($result === null) {    // Выполняем запрос из MySQL

			$sql =	"SELECT shop_rel.category_id AS id, cat.name AS name,
						(
							SELECT CONCAT('{\"', p.id, '\":', p.images, '}') FROM ".Products::TABLE." p
							INNER JOIN ".Common::TABLE_PRODUCTS_REL." pr ON (pr.product_id = p.id)
							WHERE pr.category_id = shop_rel.`category_id` ORDER BY rating DESC LIMIT 1
						) AS img,

						(
							SELECT alias FROM ".Categories::TABLE." c
							WHERE c.id = cat.parent_id
						) AS parent_alias, cat.alias AS alias, cat.parent_id AS parent_id,

						COUNT(*) AS count_prod, shop_rel.sort AS sort
						FROM ".Common::TABLE_CAT_SHOP_REL." shop_rel
						INNER JOIN ".self::TABLE." cat ON (shop_rel.category_id = cat.id)
						INNER JOIN ".Common::TABLE_PRODUCTS_REL." prod_rel ON (prod_rel.category_id = cat.id)
						INNER JOIN ".Products::TABLE." prod ON (prod.id = prod_rel.product_id)

						WHERE shop_rel.shop_id = ".$shop_id." && shop_rel.category_parent_id ".$conditional." ".$parent_id."
						GROUP BY id
						ORDER BY shop_rel.sort ".$sort;


			$result = $this->_db->query($sql)->fetchAll();

			// Сохраняем запрос в кэше
			if ($cache && $this->_cache) $backendCache->save(self::TABLE.'-'.strtolower(__FUNCTION__).'-'.$shop_id.'.cache', $result);
		}
		return $result;
	}

	public function getLeaderCategoryImage($category_id, $cache)
	{
		$result = null;
		if ($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE . '-' . implode('-', __FUNCTION__) . '.cache');
		}

		if ($result === null) {
			$sql = "SELECT products.id as product_id, products.images
					FROM products
					INNER JOIN products_relationship ON products_relationship.product_id = products.id
					WHERE category_id = $category_id
					ORDER BY rating DESC
					LIMIT 1";

			$result = $this->_db->query($sql)->fetch();

			if($result) {

				$image = json_decode($result['images'], true);
				return array('product_id' => $result['product_id'],'image' => $image[0]);
			}
			return false;
		}

		// Сохраняем запрос в кэше
		if ($cache && $this->_cache) $backendCache->save(self::TABLE . '-' . implode('-', __FUNCTION__) . '.cache', $result);
	}

	/**
	 * Формирование фильтров для выдачи
	 * @author <filchakov.denis@gmail.com>
	 * @param array $filter
	 * @param int $price_id
	 * @param bool $cache
	 * @return string
	 */
	public function renderFilter($filter = array(), $price_id = 1, $cache = false){
		$sql = "
			SELECT
			pr.tag_id,
			tag.name,
			tag.alias,
			(SELECT name FROM tags WHERE id = tag.parent_id) as 'parent_id',
			count(pr.tag_id) as 'count'
			FROM `".Products::PRODUCT_RELATION."` pr
			INNER JOIN `".Tags::TABLE."` tag ON (pr.tag_id = tag.id)
			INNER JOIN `".Products::TABLE."` p ON (p.id = pr.product_id)
			INNER JOIN `".Prices::TABLE."` price ON (p.id = price.product_id)
			INNER JOIN `".Brands::TABLE."` brand ON (brand.id = p.brand_id)";
		$sql .= ' WHERE price.id = '.$price_id;
		$filterSql = '';
		if(isset($filter['tags'])){
			$filterSql .= ' AND ';
			foreach($filter['tags'] as $tagValue){
				$tags[] = " FIND_IN_SET('".$tagValue."', filter_tags) ";
			}
			$filterSql .= implode(' AND ', $tags);
		}
		if(isset($filter['sizes'])){
			$filterSql .= ' AND ';
			foreach($filter['sizes'] as $tagValue){
				$tags[] = " FIND_IN_SET('".$tagValue."', filter_size) ";
			}
			$filterSql .= '('.implode(' OR ', $tags).')';
		}
		if(isset($filter['sex']) && is_numeric($filter['sex'])){
			$filterSql .= ' AND p.sex = '.$filter['sex'];
		}
		if(isset($filter['sale']) && is_numeric($filter['sale']) && $filter['sale']>0){
			$filterSql .= ' AND price.percent = '.$filter['sale'];
		} elseif (isset($filter['sale']) && $filter['sale']==0){
			$filterSql .= ' AND price.percent != 0';
		}
		if(isset($filter['new']) && is_numeric($filter['new'])){
			$filterSql .= ' AND p.is_new = '.$filter['new'];
		}
		if(isset($filter['brand'])){
			$filterSql .= ' AND ';
			foreach($filter['brand'] as $brandValue){
				$brand[] = " brand_id = ".$brandValue." ";
			}
			$filterSql .= ' ( '.implode(' OR ', $brand).' ) ';
		}
		if(isset($filter['price'])){
			if(isset($filter['price']['min']) && is_numeric($filter['price']['min'])){
				$filterSql .= ' AND price.discount >= '.$filter['price']['min'];
			}
			if(isset($filter['price']['max']) && is_numeric($filter['price']['max'])){
				$filterSql .= ' AND price.discount <= '.$filter['price']['max'];
			}
		}
		$sql .= $filterSql;
		if(isset($filter['category'])){
			$filterSql .= ' AND pr.category_id IN ('.implode(',',$filter['category']).')';
		}
		$topWhere = '';
		if(isset($filter['top'])){
			$topWhere .= 'INNER JOIN (SELECT id FROM products as products ';
			if(isset($filter['sex'])){
				$topWhere .= 'WHERE products.sex IN ('.$filter['sex'].')';
			}
			$topWhere .= ' GROUP BY articul ORDER BY rating DESC LIMIT 200) jp ON pr.product_id = jp.id';
		}
		$sql .=  ' AND pr.product_id IN (SELECT p.id
			FROM `products_relationship` pr
			INNER JOIN `products` p ON (p.id = pr.product_id)
			INNER JOIN `prices` price ON (p.id = price.product_id)
			INNER JOIN `brands` brand ON (brand.id = p.brand_id)
			'.$topWhere.'
			WHERE price.id = '.$price_id. '' .$filterSql.')
		';
		$sql .= ' GROUP BY pr.tag_id';
		$tagsTMP = $this->_db->query($sql)->fetchAll();
		foreach($tagsTMP as $valueTags){
			$tags['tags'][$valueTags['parent_id']]['items'][$valueTags['count']][]	= array($valueTags['name'], $valueTags['alias'], 'tag_id'=>$valueTags['tag_id']);
			krsort($tags['tags'][$valueTags['parent_id']]['items']);
		}
		$result = '';
		foreach($tags['tags'] as $nameCategory => $categoryTag){
			$result .= '<div class="group" onclick="$(\'.parent-of-sizes'.$nameCategory.'\').toggle();">'.$nameCategory.'</div>'."\r\n";
			if(is_array($tags['tags'][$nameCategory]['items'])){
				foreach($tags['tags'][$nameCategory]['items'] as $count => $tagInfo){
					foreach($tagInfo as $itemTagInfo){
						if(in_array($itemTagInfo['tag_id'],$filter['tags'])){
							$checked = 'checked="checked"';
						} else {
							$checked = '';
						}
						$result .= '
						<div class="tag parent-of-titles'.$nameCategory.'">
								<label>
									<input type="checkbox" '.$checked.' name="tags[]" value="'.$itemTagInfo[1].'">'.$itemTagInfo[0].'<span class="count"> ('.$count.')</span>
								</label>
						</div>';
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Формирование ленты товаров для категории TOP-200
	 * @author <filchakov.denis@gmail.com>
	 * @param array $filter
	 * @param int $price_id
	 * @param bool $cache
	 * @return null
	 */
	public function renderTopItemsLine($filter = array(), $price_id = 1, $cache = false){
		$result = null;
		if ($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE . '-'.md5(implode(',',$filter)).'.cache');
		}
		if ($result === null) {
			$sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM ( ';
			$offset = $filter['page'] * $filter['limit'];
			$sql .= "SELECT
			p.*,
			brand.name AS brand_name,
			price.price AS price,
			price.discount AS discount,
			price.percent AS percent
			FROM `".Products::PRODUCT_RELATION."` pr
			INNER JOIN `".Products::TABLE."` p ON (p.id = pr.product_id)
			INNER JOIN `".Prices::TABLE."` price ON (p.id = price.product_id)
			INNER JOIN `".Brands::TABLE."` brand ON (brand.id = p.brand_id)";
			if(isset($filter['top'])){
				$sql .= 'INNER JOIN (SELECT id FROM products as products ';
				if(isset($filter['sex'])){
					$sql .= 'WHERE products.sex IN ('.$filter['sex'].')';
				}
				$sql .= ' GROUP BY articul ORDER BY rating DESC LIMIT 200) jp ON pr.product_id = jp.id';
			}
			$sql .= ' WHERE price.id = '.$price_id;
			if(isset($filter['category'])){
				$sql .= ' AND pr.category_id IN ('.implode(',',$filter['category']).')';
			}
			if(isset($filter['tags'])){
				$sql .= ' AND ';
				foreach($filter['tags'] as $tagValue){
					$tags[] = " FIND_IN_SET('".$tagValue."', filter_tags) ";
				}
				$sql .= implode(' AND ', $tags);
			}
			if(isset($filter['sizes'])){
				$sql .= ' AND ';
				foreach($filter['sizes'] as $tagValue){
					$tags[] = " FIND_IN_SET('".$tagValue."', filter_size) ";
				}
				$sql .= '('.implode(' OR ', $tags).')';
			}
			if(isset($filter['sex']) && isset($filter['sale'])){
				$sex[] = 0;
				$sex[] = 3;
				$sex[] = $filter['sex'];
				$sql .= ' AND p.sex IN ('.implode(',',$sex).') ';
			} elseif (isset($filter['sex']) && is_numeric($filter['sex'])){
				$sql .= ' AND p.sex = '.$filter['sex'];
			}
			if(isset($filter['sale']) && is_numeric($filter['sale']) && $filter['sale']>0){
				$sql .= ' AND price.percent = '.$filter['sale'];
			} elseif (isset($filter['sale']) && $filter['sale']==0){
				$sql .= ' AND price.percent != 0';
			}
			if(isset($filter['new']) && is_numeric($filter['new'])){
				$sql .= ' AND p.is_new = '.$filter['new'];
			}
			if(isset($filter['brand'])){
				$sql .= ' AND ';
				foreach($filter['brand'] as $brandValue){
					$brand[] = " brand_id = ".$brandValue." ";
				}
				$sql .= ' ( '.implode(' OR ', $brand).' ) ';
			}
			if(isset($filter['price'])){
				if(isset($filter['price']['min']) && is_numeric($filter['price']['min'])){
					$sql .= ' AND price.discount >= '.$filter['price']['min'];
				}
				if(isset($filter['price']['max']) && is_numeric($filter['price']['max'])){
					$sql .= ' AND price.discount <= '.$filter['price']['max'];
				}
			}
			$sql .= ' GROUP BY articul ORDER BY rating DESC LIMIT 200) as p';
			$result['limit'] = $filter['limit'];
			$result['sortby'] = $filter['sortby'];
			$result['orderby'] = $filter['orderby'];
			if(isset($filter['sale'])){
				$result['sale'] = $filter['sale'];
			}
			if(isset($filter['sortby'])){
				switch($filter['sortby']){
					case "new": $filter['sortby'] = 'date_create'; break;
					case "price": $filter['sortby'] = 'discount'; break;
					case "discount": $filter['sortby'] = 'percent'; break;
					default: case "rating": $filter['sortby'] = 'rating'; break;
				}
			}
			$sql .= ' GROUP BY articul ORDER BY '.$filter['sortby'].' '.$filter['orderby'];
			$sql .= ' LIMIT '.$offset.', '.$filter['limit'];
			$result['items'] = $this->_db->query($sql)->fetchAll();
			$result['count'] = (int) $this->_db->query('SELECT FOUND_ROWS() as \'count\';')->fetch()['count'];
			$result['page'] = $filter['page']+1;
			$result['price'] = $filter['price'];
			if ($cache && $this->_cache) $backendCache->save(self::TABLE . '-'.md5(implode(',',$filter)).'.cache', $result);
		}
		return $result;
	}

	/**
	 * Формирование ленты товаров
	 * @author <filchakov.denis@gmail.com>
	 * @param array $filter
	 * @param int $price_id
	 * @param bool $cache
	 * @return null
	 */
	public function renderItemsLine($filter = array(), $price_id = 1, $cache = false){
		$result = null;
		if ($cache && $this->_cache) {
			$backendCache = $this->getDI()->get('backendCache');
			$result = $backendCache->get(self::TABLE . '-'.md5(implode(',',$filter)).'.cache');
		}
		if ($result === null) {
			$offset = $filter['page'] * $filter['limit'];
			$sql = "SELECT
			SQL_CALC_FOUND_ROWS
			p.*,
			brand.name AS brand_name,
			price.price AS price,
			price.discount AS discount,
			price.percent AS percent
			FROM `".Products::PRODUCT_RELATION."` pr
			INNER JOIN `".Products::TABLE."` p ON (p.id = pr.product_id)
			INNER JOIN `".Prices::TABLE."` price ON (p.id = price.product_id)
			INNER JOIN `".Brands::TABLE."` brand ON (brand.id = p.brand_id)";
			if(isset($filter['top'])){
				$sql .= 'INNER JOIN (SELECT id FROM products as products ';
				if(isset($filter['sex'])){
					$sql .= 'WHERE products.sex IN ('.$filter['sex'].')';
				}
				$sql .= ' GROUP BY articul ORDER BY rating DESC LIMIT 200) jp ON pr.product_id = jp.id';
			}
			$sql .= ' WHERE price.id = '.$price_id;
			if(isset($filter['category'])){
				$sql .= ' AND pr.category_id IN ('.implode(',',$filter['category']).')';
			}
			if(isset($filter['tags'])){
				$sql .= ' AND ';
				foreach($filter['tags'] as $tagValue){
					$tags[] = " FIND_IN_SET('".$tagValue."', filter_tags) ";
				}
				$sql .= implode(' AND ', $tags);
			}
			if(isset($filter['sizes'])){
				$sql .= ' AND ';
				foreach($filter['sizes'] as $tagValue){
					$tags[] = " FIND_IN_SET('".$tagValue."', filter_size) ";
				}
				$sql .= '('.implode(' OR ', $tags).')';
			}
			if(isset($filter['sex']) && isset($filter['sale'])){
				$sex[] = 0;
				$sex[] = 3;
				$sex[] = $filter['sex'];
				$sql .= ' AND p.sex IN ('.implode(',',$sex).') ';
			} elseif (isset($filter['sex']) && is_numeric($filter['sex'])){
				$sql .= ' AND p.sex = '.$filter['sex'];
			}
			if(isset($filter['sale']) && is_numeric($filter['sale']) && $filter['sale']>0){
				$sql .= ' AND price.percent = '.$filter['sale'];
			} elseif (isset($filter['sale']) && $filter['sale']==0){
				$sql .= ' AND price.percent != 0';
			}
			if(isset($filter['new']) && is_numeric($filter['new'])){
				$sql .= ' AND p.is_new = '.$filter['new'];
			}
			if(isset($filter['brand'])){
				$sql .= ' AND ';
				foreach($filter['brand'] as $brandValue){
					$brand[] = " brand_id = ".$brandValue." ";
				}
				$sql .= ' ( '.implode(' OR ', $brand).' ) ';
			}
			if(isset($filter['price'])){
				if(isset($filter['price']['min']) && is_numeric($filter['price']['min'])){
					$sql .= ' AND price.discount >= '.$filter['price']['min'];
				}
				if(isset($filter['price']['max']) && is_numeric($filter['price']['max'])){
					$sql .= ' AND price.discount <= '.$filter['price']['max'];
				}
			}
			$result['limit'] = $filter['limit'];
			$result['sortby'] = $filter['sortby'];
			$result['orderby'] = $filter['orderby'];
			if(isset($filter['sale'])){
				$result['sale'] = $filter['sale'];
			}
			if(isset($filter['sortby'])){
				switch($filter['sortby']){
					case "new": $filter['sortby'] = 'p.date_create'; break;
					case "price": $filter['sortby'] = 'price.discount'; break;
					case "discount": $filter['sortby'] = 'price.percent'; break;
					default: case "rating": $filter['sortby'] = 'p.rating'; break;
				}
			}
			$sql .= ' GROUP BY articul ORDER BY '.$filter['sortby'].' '.$filter['orderby'];
			$sql .= ' LIMIT '.$offset.', '.$filter['limit'];
			$result['items'] = $this->_db->query($sql)->fetchAll();
			$result['count'] = (int) $this->_db->query('SELECT FOUND_ROWS() as \'count\';')->fetch()['count'];
			$result['page'] = $filter['page']+1;
			$result['price'] = $filter['price'];
			if ($cache && $this->_cache) $backendCache->save(self::TABLE . '-'.md5(implode(',',$filter)).'.cache', $result);
		}
		return $result;
	}

	/**
	 * Построение URL по массиву параметров
	 * @author <filchakov.denis@gmail.com>
	 */
	public function buildUrl($param = array()){
		$result = array();
		$gender = array(
			1 => 'man',
			2 => 'woman'
		);
		$psevdo = array();
		if(isset($param['new'])){
			$psevdo[] = 'new';
		}
		if(isset($param['top'])){
			$psevdo[] = 'top';
		}
		if(isset($param['sex'])){
			//array_unshift($result, $gender[$param['sex']]);
			$psevdo[] = $gender[$param['sex']];
		}
		if(isset($param['category'])){
			$category = $this->_db->query('SELECT alias FROM '.self::TABLE.' WHERE id in ('.implode(',',$param['category']).')')->fetchAll();
			foreach($category as $value){
				$result[] = urlencode($value['alias']);
			}
		}
		if(isset($param['tags'])){
			$tags = $this->_db->query('SELECT alias FROM '.Tags::TABLE.' WHERE id in ('.implode(',',$param['tags']).')')->fetchAll();
			foreach($tags as $value){
				$result[] = urlencode($value['alias']);
			}
		}
		$result = array_merge($psevdo, $result);
		if($param['page']==0){
			$param['page'] = 1;
		}
		$getParam = array(
			'page' => $param['page'],
			'limit' => $param['limit'],
			'sortby' => $param['sortby'],
			'orderby' => $param['orderby']
		);
		return $result = implode('/',$result).'?'.http_build_query($getParam);
	}

}