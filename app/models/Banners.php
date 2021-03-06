<?php
namespace Models;

/**
 * Class Banners Модель для `structure_banners`
 *
 * Получает идентификатор соединения $this->_db = $this->getReadConnection();
 *
 * @package Banners
 * @subpackage Models
 */
class Banners extends \Phalcon\Mvc\Model
{
	/**
	 * Таблица в базе
	 * @const
	 */
	const TABLE = 'structure_banners';

	const STATUS__ACTIVE = 1;

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

	public function getBanners($site_id, $cache) {

		$result = null;
		if ($cache && $this->_cache) {
			$_cache = $this->getDI()->get('backendCache');
			$result = $_cache->get(strtolower(__FUNCTION__) . '-'.implode('_', $site_id . self::STATUS__ACTIVE).'.cache');
		}

		if ($result === null) {

			$sql = "SELECT id, site_id, `type`,
					status, href, image
					 FROM ".self::TABLE." banner
					 WHERE site_id = " . (int)$site_id .
					 " AND status = " . self::STATUS__ACTIVE;

			$result = $this->_db->query($sql)->fetchAll();
			$banners = array('main' => array(), 'inner' => array());
			foreach ($result as $banner) {
				if ($banner['type'] == 1) {
					$banners['main'][] = $banner;
				} else {
					$banners['inner'][] = $banner;
				}
			}

			// Сохраняем запрос в кэше
			if ($cache && $this->_cache) $_cache->save(strtolower(__FUNCTION__) . '-' . implode('_', $site_id . self::STATUS__ACTIVE) . '.cache', $result);
		}
		return $banners;
	}
}