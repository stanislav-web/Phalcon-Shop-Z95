<?php
	namespace Helpers;
	use \Phalcon\Http\Request,
		\Phalcon\Mvc\Router,
		Phalcon\Tag;

	/**
	 * Class Cart Помощник для работы с корзиной
	 *  @package Phalcon
	 * @subpackage Helpers
	 */
	class Cart extends  Tag
	{

		/**
		 * Поля для корзины по которым происходит фильтрация
		 * @var array
		 */
		public static $fields	=	[
			'product_id',
			'name',
			'articul',
			'images',
			'price',
			'discount',
			'brand_name',
			'brand_alias',
			'category_alias',
			'percent',
			'filter_size',
			'size',
		];

		/**
		 * Добавление нового товара к существующему или обновление списка
		 *
		 * @param  array   $items
		 * @param int $item_id новый товар
		 * @access static
		 * @return array
		 */
		public static function pushItem($items, $item_id = null)
		{
			if(!empty($items))
			{
				$itemsKeys = array_keys($items);

				if(!in_array($item_id, $itemsKeys))
				{
					array_push($itemsKeys, $item_id);
					$result = $itemsKeys;
				}
				else $result =  $itemsKeys;
			}
			else $result = [$item_id];

			return array_filter($result);
		}

		/**
		 * Проверка переполнения вещами в корзине
		 *
		 * @param     $items
		 * @param int $itemsLimit
		 * @access static
		 * @return bool
		 */
		public static function overflowItems($items = [], $itemsLimit = 10)
		{
			if(!empty($items))
			{
				if(sizeof($items) > $itemsLimit)
					return true;
			}
			return false;
		}

		/**
		 * Проверка переполнения размерами в корзине
		 *
		 * @param     $items
		 * @param int $sizeLimit
		 * @access static
		 * @return bool
		 */
		public static function overflowSizes($items, $sizeLimit = 10)
		{
			if(!empty($items))
			{
				foreach($items as $id => $properties)
				{
					if(!empty($properties['size']))
					{
						foreach($properties['size'] as $size => $count)
						{
							// корзина переполнена размерами
							if($count > $sizeLimit)
								return true;
						}
					}
				}
			}
			return false;
		}

		/**
		 * Фильтрация корзины, проверка нужных полей, обновление товаров, их количства и цен
		 * @param array $dbData
		 * @param array $postData
		 * @param array $sessionData
		 * @return array
		 */
		public static function filter( array $dbData = [], array $postData = [], $sessionData = [])
		{
			$result	=	[];
			// преобразую вариант из базы
			if(!empty($dbData))
			{
				$dbData = Catalogue::arrayToAssoc($dbData, 'product_id');

				// добавляю размеры
				if(isset($postData['product_id']) && !empty($postData['product_id']))
					$dbData[$postData['product_id']]['size'] = $postData['size'];

				// фильтрую поля из базы по self::$fields для корзины
				$filterFields	=	array_flip(self::$fields);

				foreach($dbData as $product_id => $properties)
				{
					foreach($properties as $key => $value)
					{
						if($key == 'images')
							$dbData[$product_id][$key]	=	json_decode($dbData[$product_id][$key], true);
						if(!isset($filterFields[$key]))
							unset($dbData[$product_id][$key]);
					}
				}
				$result['items'] = $dbData;

				// проверяю существующие элементы и обновляю рамеры если нашелся такой же product_id
				if(!empty($sessionData))
					$result = array_replace_recursive($sessionData, $result);
			}
			elseif(!empty($sessionData))
				$result	=	$sessionData;
			else
				return ['meta'	=>	['total' => 0, 'sum' => 0]];

			if(isset($result['items']) && !empty($result['items']))
			{
				// считаю сумму покупок по скидке товара `discount`

				foreach($result['items'] as $product_id => $properties)
				{
					// проверка ключа со строгой типизацией, так как размер есть и нулевой, поэтому привожу результат к строке
					$key = (string)array_search("0", $result['items'][$product_id]['size'], true);

					if($key != '')
						unset($result['items'][$product_id]['size'][$key]);

					// проверяю, есть ли ключи (размеры) внутри [size]

					if(sizeof(array_keys($result['items'][$product_id]['size'])) > 0)
					{
						$result['items'][$product_id]['meta']	=  call_user_func(function() use ($properties) {

							$total	=	0;
							$price	=	0;

							if($properties['size'] != '')
							{
								foreach($properties['size'] as $size => $count)
									if($size !=='?') $total	+=	$count;

								$price 	=	(!empty($properties['discount'])) ? $properties['discount'] : $properties['price'];
							}
							return ['total' => $total, 'sum' => $total*$price];
						});
					}
					else  // уничтожаю товар так как в нем нет размеров
						unset($result['items'][$product_id]);
				}

				// подсчитываю общую мета информацию по корзине, (сколько в сумме и по сколько)
				if(!empty($result['items']))
				{
					$result['meta']	=	call_user_func(function() use ($result) {

						$total	=	0;
						$sum	=	0;

						foreach($result['items'] as $product_id => $properties)
							if(!empty($properties['meta'])) {
								$total	+=	$properties['meta']['total'];
								$sum	+=	$properties['meta']['sum'];
							}
						return ['total' => $total, 'sum' => $sum];
					});
				}
			}
			return $result;
		}

		/**
		 * Получение следующего уровня скиди на товар
		 * в зависимости от суммы покупок в корзине
		 *
		 * @param json $discounts набор скидок магазина
		 * @param array $meta параметры корзины
		 * @return array $result
		 */
		public static function getMaxDiscount($discounts, array $meta)
		{
			$result = [
				'current'	=>	0,
				'next'		=>	0,
			];

			$meta	=	[
				'sum' 	=> (isset($meta['sum'])) 	? $meta['sum'] : 0,
				'total' => (isset($meta['total'])) 	? $meta['total'] : 0,
			];

			$date = strtotime(date('Y-m-d'));
			if(!is_array($discounts))
				$discounts = json_decode($discounts, true);


			// проверяю скидку по количеству товара
			if(isset($discounts['count']) && $discounts['count'] !='')
			{

				$d = array_flip($discounts['count']);

				$previous = 0;
				foreach($d as $percent => $count)
				{
					if($count >= $meta['total'])
						$result['count']['current']	= $previous;

					if($count > $meta['total'])
					{
						$result['count']['next']	=	$percent;
						break;
					}
					else // значит максимальная скидка по количеству уже есть
						$result['count']	=	[
							'current'	=>	array_pop(array_flip($d)),
							'next'		=>	array_pop(array_flip($d))
						];
					$previous	=	$percent;
				}
			}

			// проверяю скидку по сумме товара
			if(isset($discounts['sum']) && $discounts['sum'] !='')
			{

				$d = array_flip($discounts['sum']);

				$previous = 0;
				foreach($d as $percent => $count)
				{
					if($count >= $meta['sum'])
						$result['sum']['current']	= $previous;
					if($count > $meta['sum'])
					{
						$result['sum']['next']	=	$percent;
						break;
					}
					else // значит максимальная скидка по сумме уже есть
						$result['sum']	=	[
							'current'	=>	array_pop(array_flip($d)),
							'next'		=>	array_pop(array_flip($d))
						];
					$previous	=	$percent;
				}
			}

			// проверяю приоритеты скидок и даю выгодную в зависимости от расчета суммы покупок и количества
			if(!isset($result['sum']))
			{
				$result['current']	=	(isset($result['count']['current'])) ? $result['count']['current'] : 0;
				$result['next']		=	(isset($result['count']['next'])) ? $result['count']['next'] : 0;
				$result['type']		=	'count';
				$result['board']	=	(isset($discounts['count'])) ? array_flip($discounts['count']) : [];
			}
			elseif(!isset($result['count']))
			{
				$result['current']	=	(isset($result['sum']['current'])) ? $result['sum']['current'] : 0;
				$result['next']		=	(isset($result['sum']['next'])) ? $result['sum']['next'] : 0;
				$result['type']		=	'sum';
				$result['board']	=	(isset($discounts['sum'])) ? array_flip($discounts['sum']) : [];
			}
			else
			{
				if($result['count']['current'] > $result['sum']['current'])
					$result['current']	=	$result['count']['current'];
				else
					$result['current']	=	$result['sum']['current'];

				if($result['count']['next'] > $result['sum']['next'])
				{
					$result['next']		=	$result['count']['next'];
					$result['type']		=	'count';
					$result['board']	=	array_flip($discounts['count']);
				}
				else
				{
					$result['next']	=	$result['sum']['next'];
					$result['type']	=	'sum';
					$result['board']	=	array_flip($discounts['sum']);
				}
			}
			unset($result['count'], $result['sum']);
			// если это максимальная скидка, то удаляю следующий уровень
			if($result['current'] == $result['next'])
				unset($result['next']);

			// получаю полну сумму со скидкой
			if(isset($result['type']))
				$result['discount_sum']	=	($meta['sum']  - ($meta['sum'] * $result['current'])/100);

			// проверяю дату действия

			if(isset($discounts['period']['start']) && isset($discounts['period']['end']))
			{
				// расчет в промежуток времени

				$start = strtotime($discounts['period']['start']);
				$end = strtotime($discounts['period']['end']);
				if($date > $start && $date < $end)
					return $result;
				else	// дата прошла , но она еще стоит в настройках.. значит скидки закончились
					return [
						'current'	=>	0,
						'next'		=>	0,
					];
			}
			else // действует как постоянная скидка от суммы
				return $result;
		}

		/**
		 * isSizeHere(array $cart = [], $product_id, $size) Проверка товара с размером на присутствие в корзине
		 *
		 * @param array $cart	товары в корзине (из сессии)
		 * @param       $product_id сравниваемый товар
		 * @param       $size сравниваемый размер
		 * @return bool
		 */
		public static function isSizeHere(array $cart = [], $product_id, $size)
		{
			if(isset($cart['items']) && !empty($cart['items']))
				if(isset($cart['items'][$product_id]['size'][$size]))
				return true;
			return false;
		}

		/**
		 * isSizeHere(array $cart = [], $product_id, $size) Проверка товара с размером на присутствие в корзине
		 *
		 * @param array $cart	товары в корзине (из сессии)
		 * @param int  $product_id проверяемый товар
		 * @return int
		 */
		public static function countBoughtSizes($cart, $product_id)
		{
			if(isset($cart['items'][$product_id]['size']))
				return array_sum($cart['items'][$product_id]['size']);
			else return 0;
		}
	}