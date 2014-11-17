<?php
	namespace Mappers;
	use Helpers\Catalogue,
		Phalcon\Mvc\View,
		Models;

	/**
	 * Class Router  Маршрутизатор выдачи товаров
	 *
	 * @package Shop
	 * @subpackage Mappers
	 * @author Stanislav WEB
	 * @example <code>
	 *          (new Router())	->setRules($action)
	 *          	->setCollection($collection)
	 *				->setExclude(['top' => 'ТОП 200', 'favorites' => 'Понравилось', 'new' => 'Новинки', 'sale' => 'Распродажа'])
	 *				->setNav($this->_breadcrumbs)
	 *				->render();
	 *          </code>
	 */
	class Router extends \Phalcon\Mvc\Controller
	{
		private

			/**
			 * Модель для работы
			 * @see рекомендую передавать модель чтобы использовать
			 *        уже готовое соединение
			 * @var object
			 */
			$_model = false,

			/**
			 * Параметры магазина
			 * @var object
			 */
			$_shop = false,

			/**
			 * Входящий набор для маршрутизации
			 * @var object
			 */
			$_rules = false,

			/**
			 * Половая принадлежность
			 * @var int
			 */
			$_gender = 0,

			/**
			 * Фильтр. FALSE
			 * @var array
			 */
			$_filter = [],

			/**
			 * Категории: набор исключений (для виртуальных категорий)
			 * @var array
			 */
			$_exclude = [],

			/**
			 * Коллекция всех категорий
			 * @var array
			 */
			$_collection = [],

			/**
			 * Навигационная цепочка
			 * @var \Breadcrumbs\Breadcrumbs
			 */
			$_breadcrumbs = false,

			/**
			 * Мастер переводов
			 * @var \Phalcon\Translate\Adapter\NativeArray
			 */
			$_translate = false,

			/**
			 * Заголовок: состояние по умолчанию
			 * @var bool
			 */
			$_title = false,

			/**
			 * Шаблон выдачи: состояние по умолчанию
			 * @var bool
			 */
			$_template = false,

			/**
			 * Шаблон выдачи: состояние по умолчанию
			 * @var bool
			 */
			$_items = false,

			/**
			 * Вывод товаров на страницу
			 * @var int
			 */
			$_limit = 100,

			/**
			 * Пагинация: состояние по умолчанию
			 * @var bool
			 */
			$_pagination = false,

			/**
			 * Json выдача
			 * @var bool
			 */
			$_isJson = false,

			/**
			 * Обозначение полов
			 * @var array
			 */
			$_sex = [
			'UNI' => 0, 'MAN' => 1, 'WOMAN' => 2, 'KIDS' => 3
		];

		/**
		 * Установка модели
		 * @param \Models\Products $model
		 * @return \Mappers\Router
		 */
		public function setModel(\Models\Products $model)
		{
			$this->_model = $model;
			return $this;
		}

		/**
		 * Установка правил маршрута
		 * @param stdClass $rules
		 * @return \Mappers\Router
		 */
		public function setRules($rules)
		{
			$this->_rules = $rules;
			return $this;
		}

		/**
		 * Установка правил магазина
		 * @param array $shop
		 * @return \Mappers\Router
		 */
		public function setShop(array $shop)
		{
			$this->_shop = $shop;
			return $this;
		}

		/**
		 * Установка коллекции всех категорий с параметрами
		 * @param array $collection
		 * @return \Mappers\Router
		 */
		public function setCollection(array $collection)
		{
			$this->_collection = $collection;
			return $this;
		}

		/**
		 * Установка заголовка
		 * @param string $title
		 * @access protected
		 * @return \Mappers\Router
		 */
		protected function setTitle($title, $rewrite = false)
		{
			$this->_title = $title;
			if (!$rewrite)
				$this->tag->prependTitle($this->_translate[$title] . ' - ');
			else $this->tag->setTitle($title);
			return $this;
		}

		/**
		 * Установка навигационной цепочки
		 * @param \Breadcrumbs\Breadcrumbs $breadcrumbs
		 * @return \Mappers\Router
		 */
		public function setNav(\Breadcrumbs\Breadcrumbs $breadcrumbs)
		{
			$this->_breadcrumbs = $breadcrumbs;
			return $this;
		}

		/**
		 * Установка навигационной цепочки
		 * @param \Phalcon\Translate\Adapter\NativeArray $translate
		 * @return \Mappers\Router
		 */
		public function setTranslate(\Phalcon\Translate\Adapter\NativeArray $translate)
		{
			$this->_translate = $translate;
			return $this;
		}

		/**
		 * Установка шаблона для вывода
		 * @param string $template
		 * @return \Mappers\Router
		 */
		public function setTemplate($template)
		{
			$this->_template = $template;
			return $this;
		}

		/**
		 * Определение ID пола
		 * @param array $string
		 * @return \Mappers\Router
		 */
		protected function setGender($array)
		{
			if (in_array('man', $array))
				$this->_gender = $this->_sex['MAN'];
			else if (in_array('men', $array))
				$this->_gender = $this->_sex['MAN'];
			else if (in_array('women', $array))
				$this->_gender = $this->_sex['WOMAN'];
			else if (in_array('woman', $array))
				$this->_gender = $this->_sex['WOMAN'];
			else if (in_array('kids', $array))
				$this->_gender = $this->_sex['KIDS'];
			else
				$this->_gender = 0;

			return $this;
		}

		/**
		 * Получить ID пола по алиасу
		 * @param string $key
		 * @return int key
		 */
		protected function getGender($key)
		{
			$gender = ['man'   => $this->_sex['MAN'],
					   'men'   => $this->_sex['MAN'],
					   'woman' => $this->_sex['WOMAN'],
					   'women' => $this->_sex['WOMAN'],
					   'kids'  => $this->_sex['KIDS'],
			];
			return (array_key_exists($key, $gender)) ? $gender[$key] : false;
		}

		/**
		 * Установка шаблона для вывода
		 * @param array $items
		 * @return \Mappers\Router
		 */
		public function setItems(array $items)
		{
			$this->_items = $items;
			return $this;
		}

		/**
		 * Установка исключений
		 * @param array $items
		 * @return \Mappers\Router
		 */
		public function setExclude(array $exclude)
		{
			$this->_exclude = $exclude;
			return $this;
		}

		/**
		 * Отображаем каталог как json ответ
		 * @return \Mappers\Router
		 */
		public function json()
		{
			$this->_isJson = true;

			$this->view->disable();
			$this->response->setContentType('application/json', 'UTF-8');

			// отключаю лишние представления
			$this->view->disableLevel([

				View::LEVEL_LAYOUT      => true,
				View::LEVEL_MAIN_LAYOUT => true
			]);
			return $this;
		}

		/**
		 * Переключатель режима отображения виртуальных категорий
		 * @param string $type
		 * @access public
		 * @return array
		 */
		public function virtualSwitcher($type)
		{
			switch ($type['alias']) {
				case 'new':            // Новые

					if (!isset($this->_rules->query['sort']))
						$this->_rules->query['sort'] = 'prod.date_income';
					$this->_rules->query['order'] = 'desc';

					$items = $this->_model->getProducts([
							'prod.is_new = ' => '1',
							'price.price > ' => '0',
							'price.id ='     => $this->_shop['price_id'],
							'prod.sex = '    => ($this->_gender > 0) ? $this->_gender : false,
						],
						$this->_rules->query, true);

					break;

				case 'top':            // Топ 200

					// проверяю на фильтры gender
					$gender = end($this->_rules->catalogue);

					if ($gender != 'top') {
						$this->_filter = [
							'prod.sex = ' => $this->getGender($gender),
						];
						$this->setTitle(strtoupper($gender));
					}

					// гребу топ, устанавливаю макс. число для выдачи, тем самым отключаю для топа постраничный вывод
					$this->_limit = 200;
					$items = $this->_model->getTopProducts($this->_shop['price_id'], $this->_filter, $this->_limit, true);

					break;

				case 'sales':        // Распродажи

					// проверяю на фильтры gender
					$gender = end($this->_rules->catalogue);
					if ($gender != 'sales') {

						$this->_rules->query = [
							'sex'        => [0, 3, $this->getGender($gender)],
							'discount >' => '0',
							'percent'    => $this->_rules->query['percent'],
						];

						$this->setTitle(strtoupper($gender));
					}

					// гребу выдачу

					if (!isset($this->_rules->query['sort'])) {
						$this->_rules->query['sort'] = '(price.price/price.percent)';
						$this->_rules->query['order'] = 'desc';
					}

					$items = $this->_model->getProducts([
							'price.id =' => $this->_shop['price_id'],
						],
						$this->_rules->query, true);

					break;

				case 'brands':        // Бренды

					$brand_id = $this->_rules->current;

					// гребу выдачу

					$items = $this->_model->getProducts([
						'brand.id = ' => $brand_id,
					], null, true);

					$this->title = $items[0]['brand_name'];

					$this->setTitle(strtoupper($items[0]['brand_name']));
					break;

				default :
				case 'top';
					break;
			}
			return $items;
		}

		/*
		 * Вывод результатов
		 * @param object 	$rules 		правила для маппера
		 * @param array  	$collection коллекция всех категорий
		 * @param array 	$exclude 	исключения: вирт. категории
		 * @return null
		 */
		public function render($model = false, array $shop = [], $rules = false, array $collection = [], array $exclude = [], $json = false)
		{

			// устанавливаю параметры магазина

			if (!$this->_model)
				$this->setModel($model);

			// устанавливаю параметры магазина

			if (!$this->_shop)
				$this->setShop($shop);

			// устанавливаю правила, если их не задали

			if (!$this->_rules)
				$this->setRules($rules);

			// устанавливаю все категории с их описанием

			if (!$this->_collection)
				$this->setCollection($collection);

			// устанавливаю режим выдачи

			if ($json == true)
				$this->setJson(true);

			// устанавливаю исключения (предусмотрена возможность если их и не будет)

			if (!$this->_exclude && !empty($exclude))
				$this->setExclude($exclude);

			// определяю категорию отображения
			// сначала проверяю ее в виртуальных, так как их меньше


			$intersect = array_intersect($this->_rules->catalogue, array_values(array_flip($this->_exclude)));

			if(isset($this->_exclude[$this->_rules->current]) || !empty($intersect))
			{
				// ОБРАБОТКА ВИРТУАЛОК
				$this->session->remove('category');
				$category = [
					'name'  => $this->_exclude[$intersect[0]],
					'alias' => $intersect[0]
				];

				if (isset($category['alias']))
					$items = $this->{'_set' . ucfirst($category['alias'])}();
			}
			else
			{
				// ОБРАБОТКА КАТЕГОРИЙ
				$items = $this->_setCatalogue();
			}


			// если есть в массиве счетчик удаляю его
			if (isset($items['count']))
				$count = array_pop($items);
			else $count = sizeof($items);


			// постраничная навигация
			if($count > $this->_limit)
			{
				$current = $this->request->getQuery("page");
				$this->_pagination = [
					'limit'   => $this->_limit,
					'current' => ($current) ? $current : 1,
					'count'   => $count,
					'pages'   => floor($count / $this->_limit)
				];
			}

			$this->session->set('query', $this->_rules->query);
			$this->session->set('request_uri', $this->_rules->path);

			// задаю шаблон для вывода результата выдачи
			$this->setItems($items)->setTemplate('itemsline');

			// Устанавливаю мета данные

			$this->setTitle($this->_title);

			$templateVars = [
				'title'      => $this->_title,
				'template'   => $this->_template,
				'items'      => $this->_items,
				'pagination' => $this->_pagination,
				'query'      => $this->_rules->query,
				'count'      => (isset($count)) ? $count : 0,
				'favorites'  => $this->session->get('favorites'),
				'category'   => $this->session->get('category')
			];

			if ($this->_isJson) {
				// создаю Json контекст

				$this->response->setJsonContent([
					'response' => $this->view->getRender('partials/catalogue', $this->_template, $templateVars)
				]);

				// отправляю ответ
				$this->response->send();
			} else {
				// передача переменных в шаблон
				$this->view->setVars($templateVars);

				// рендеринг шаблона
				$this->view->pick("catalogue/index");
			}
		}

		/**
		 * _setBrands() Обработка для выдачи по брендам
		 * @access private
		 * @return array
		 */
		private function _setBrands()
		{
			// корректирую мета данные
			$this->_breadcrumbs->reset();
			$this->_breadcrumbs
				->add($this->_translate['MAIN'], '/')
				->add($this->_translate['CATALOGUE'], 'catalogue')
				->add($this->_translate['BRANDS'], 'catalogue/brands');

			$this->_title = $this->_translate['BRANDS'];

			// фильтрую пол
			$this->setGender($this->_rules->catalogue);

			$this->_rules->query['brands'] = $this->_rules->current;

			if (!empty($this->_gender)) {
				$title = array_flip($this->_sex);
				$this->_title = $this->_title . ' - ' . $this->_translate[$title[$this->_gender]];

				$this->_rules->query['sex'] = $this->_gender;
			}


			if (!isset($this->_rules->query['sort'])) {
				$this->_rules->query = array_merge($this->_rules->query, [
					'sort' => [
						'rating' => 'desc',
					]
				]);
			}

			$items = $this->_model->getProducts($this->_rules->query, true);

			if(!empty($items)) {
				$this->_breadcrumbs->add($items[0]['brand_name']);
				$this->_title = $items[0]['brand_name'];
			}
			return $items;
		}

		/**
		 * _setNew() Обработка для Новинок
		 * @access private
		 * @return array
		 */
		private function _setNew()
		{
			// корректирую мета данные
			$this->_breadcrumbs->reset();
			$this->_breadcrumbs
				->add($this->_translate['MAIN'], '/')
				->add($this->_translate['CATALOGUE'], 'catalogue');

			$this->_title = $this->_translate['NEW'];

			// фильтрую пол
			$this->setGender($this->_rules->catalogue);

			$this->_rules->query['is_new'] = 1;


			if (!empty($this->_gender)) {
				$title = array_flip($this->_sex);
				$this->_title = $this->_title . ' - ' . $this->_translate[$title[$this->_gender]];

				$this->_rules->query['sex'] = $this->_gender;
			}

			if (!isset($this->_rules->query['sort'])) {
				$this->_rules->query = array_merge($this->_rules->query, [
					'sort' => [
						'date_income' => 'desc',
					]
				]);
			}
			$this->_breadcrumbs->add($this->_title);

			// Устанавливаю заголовок
			$items = $this->_model->getProductsDiscount($this->_rules->query, true);
			return $items;
		}

		/**
		 * _setSales() Обработка для Распродажи
		 * @access private
		 * @return array
		 */
		private function _setSales()
		{
			// корректирую мета данные
			$this->_breadcrumbs->reset();
			$this->_breadcrumbs
				->add($this->_translate['MAIN'], '/')
				->add($this->_translate['CATALOGUE'], 'catalogue');

			$this->_title = $this->_translate['SALE'];

			// фильтрую пол
			$this->setGender($this->_rules->catalogue);

			if (!empty($this->_gender)) {
				$title = array_flip($this->_sex);
				$this->_title = $this->_title . ' - ' . $this->_translate[$title[$this->_gender]];

				$this->_rules->query['sex'] = [0, 3, $this->_gender];
			}

			if (!isset($this->_rules->query['sort'])) {
				$this->_rules->query = array_merge($this->_rules->query, [
					'sort' => [
						'rating' => 'desc',
						'price'  => 'asc',
					]
				]);
			}

			if (!isset($this->_rules->query['percent'])) {
				$this->_rules->query['sort'] = [
					'percent' => 'desc',
					'rating'  => 'desc',
				];
			}
			$this->_breadcrumbs->add($this->_title);

			$items = $this->_model->getProductsDiscount($this->_rules->query, true);
			return $items;
		}


		/**
		 * _setTop() Обработка для Топов 200
		 * @access private
		 * @return array
		 */
		private function _setTop()
		{
			// корректирую мета данные
			$this->_breadcrumbs->reset();
			$this->_breadcrumbs
				->add($this->_translate['MAIN'], '/')
				->add($this->_translate['CATALOGUE'], 'catalogue');

			$this->_title = $this->_translate['TOP'];

			// фильтрую пол
			$this->setGender($this->_rules->catalogue);

			if (!empty($this->_gender)) {
				$title = array_flip($this->_sex);
				$this->_title = $this->_title . ' - ' . $this->_translate[$title[$this->_gender]];

				$this->_rules->query = array_merge($this->_rules->query, [
					'sex' => $this->_gender,
				]);
			}

			$this->_breadcrumbs->add($this->_title);

			$items = $this->_model->getTopProducts($this->_rules->query, 200);
			return $items;
		}


		/**
		 * _setCatalogue() Обработка выдачи для каталогов
		 * @access private
		 * @return array
		 */
		private function _setCatalogue()
		{

			// корректирую мета данные
			$this->_breadcrumbs->reset();
			$this->_breadcrumbs
				->add($this->_translate['MAIN'], '/')
				->add($this->_translate['CATALOGUE'], 'catalogue');

			// фильтрую пол
			$this->setGender($this->_rules->catalogue);

			// 	ищем родителя категории, (уже сброшены, поэтому родитель всегда 0)
			$parent = Catalogue::findInTree($this->_collection, 'alias', $this->_rules->catalogue[0])[0];


			// поиск по полу и алиасу в массиве
			if($this->_gender > 0) // поиск с полом
				$category = Catalogue::findInTree($this->_collection, 'alias', $this->_rules->catalogue[1], 'sex', $this->_gender)[0];
			else
				$category = Catalogue::findInTree($this->_collection, 'alias', $this->_rules->catalogue[1])[0];

			// непутевая ситуация... если нам выдало,
			// что категория для выборки товара у нас называется как пол человека, мы должны теперь найти ее parent_id
			// и узнать реальную картину, снова поискать в дереве и пересобрать

			if ($this->getGender($this->_rules->catalogue[1])) {
				// узнаем ID родителя
				$parent = Catalogue::findInTree($this->_collection, 'alias', $this->_rules->catalogue[0])[0];
				// пол нам известен, id родителя тоже... теперь найдем id реальной категории и вперед на поиск

				$category = Catalogue::findInTree($this->_collection, 'parent_id', $parent['id'], 'sex', $this->_gender)[0];
			}
			$this->_breadcrumbs;

			// добавляю в навигацию
			$this->_breadcrumbs->add($parent['name'], 'catalogue/' . $parent['alias'])
								->add($category['name'], 'catalogue/' . $category['alias']);

			$items = $this->_model->getProductsCategories([
					'rel.category_id  	= ' => $category['id'],
					'prod.sex = '             => ($this->_gender > 0) ? $this->_gender : false,
				],
				$this->_rules->query, true);

			$this->_title	=	$category['name'];

			// сохраняю связь чтобы не дергать из MySQL. Записываю параметры текущей категории в сессию
			// она каждый раз перезаписывается по мере новой загрузки категории, поэтому не стоит волноваться
			// это будет главный ключ используемый для связи тегов и категорий
			// {id:31848, parent_id:394, sex:0, name:Браслеты, alias:bracelet, sort:0, description:}
			$this->session->set('category', $category);

			// Устанавливаю заголовок
			return $items;
		}
	}
