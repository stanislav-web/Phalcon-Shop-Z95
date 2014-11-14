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
			 * 		уже готовое соединение
		 	 * @var object
		 	 */
			$_model	= false,

			/**
			 * Параметры магазина
		 	 * @var object
		 	 */
			$_shop	= false,

			/**
			 * Входящий набор для маршрутизации
		 	 * @var object
		 	 */
			$_rules	= false,

			/**
		 	 * Половая принадлежность
		 	 * @var int
		 	 */
			$_gender	= 0,

			/**
		 	 * Фильтр. FALSE
		 	 * @var array
		 	 */
			$_filter	= [],

			/**
			 * Категории: набор исключений (для виртуальных категорий)
		 	 * @var array
		 	 */
			$_exclude	=	[],

			/**
			 * Коллекция всех категорий
		 	 * @var array
		 	 */
			$_collection	=	[],

			/**
			 * Навигационная цепочка
		 	 * @var \Breadcrumbs\Breadcrumbs
		 	 */
			$_breadcrumbs	=	false,

			/**
		 	 * Мастер переводов
		 	 * @var \Phalcon\Translate\Adapter\NativeArray
		 	 */
			$_translate	=	false,

			/**
		 	 * Заголовок: состояние по умолчанию
		 	 * @var bool
		 	 */
			$_title		=	false,

			/**
			 * Шаблон выдачи: состояние по умолчанию
		 	 * @var bool
		 	 */
			$_template	=	false,

			/**
		 	 * Шаблон выдачи: состояние по умолчанию
		 	 * @var bool
		 	 */
			$_items	=	false,

			/**
			 * Вывод товаров на страницу
		 	 * @var int
		 	 */
			$_limit	=	100,

			/**
			 * Пагинация: состояние по умолчанию
		 	 * @var bool
		 	 */
			$_pagination	=	false,

			/**
			 * Json выдача
		 	 * @var bool
		 	 */
			$_isJson		=	false;

	/**
	 * Установка модели
	 * @param \Models\Products $model
	 * @return \Mappers\Router
	 */
	public function setModel(\Models\Products $model)
	{
		$this->_model	=	$model;
		return $this;
	}

	/**
	 * Установка правил маршрута
	 * @param stdClass $rules
	 * @return \Mappers\Router
	 */
	public function setRules($rules)
	{
		$this->_rules	=	$rules;
		return $this;
	}

	/**
	 * Установка правил магазина
	 * @param array $shop
	 * @return \Mappers\Router
	 */
	public function setShop(array $shop)
	{
		$this->_shop	=	$shop;
		return $this;
	}

	/**
	 * Установка коллекции всех категорий с параметрами
	 * @param array $collection
	 * @return \Mappers\Router
	 */
	public function setCollection(array $collection)
	{
		$this->_collection	=	$collection;
		return $this;
	}

	/**
	 * Установка заголовка
	 * @param string $title
	 * @access protected
	 * @return \Mappers\Router
	 */
	protected function setTitle($title)
	{
		$this->_title	=	$title;
		$this->tag->prependTitle($this->_translate[$title].' - ');

		return $this;
	}

	/**
	 * Установка навигационной цепочки
	 * @param \Breadcrumbs\Breadcrumbs $breadcrumbs
	 * @return \Mappers\Router
	 */
	public function setNav(\Breadcrumbs\Breadcrumbs $breadcrumbs)
	{
		$this->_breadcrumbs	=	$breadcrumbs;
		return $this;
	}

	/**
	 * Установка навигационной цепочки
	 * @param \Phalcon\Translate\Adapter\NativeArray $translate
	 * @return \Mappers\Router
	 */
	public function setTranslate(\Phalcon\Translate\Adapter\NativeArray $translate)
	{
		$this->_translate	=	$translate;
		return $this;
	}

	/**
	 * Установка шаблона для вывода
	 * @param string $template
	 * @return \Mappers\Router
	 */
	public function setTemplate($template)
	{
		$this->_template	=	$template;
		return $this;
	}

	/**
	 * Определение ID пола
	 * @param array $string
	 * @return \Mappers\Router
	 */
	protected function setGender($array)
	{
		if(in_array('man', $array))
			$this->_gender	=	1;
		else if(in_array('men', $array))
			$this->_gender	=	1;
		else if(in_array('women', $array))
			$this->_gender	=	2;
		else if(in_array('woman', $array))
			$this->_gender	=	2;
		else if(in_array('kids', $array))
			$this->_gender	=	3;
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
		$gender = ['man' 	=> '1',
				   'men' 	=> '1',
				   'woman' 	=> '2',
				   'women' 	=> '2',
				   'kids' 	=> '3'
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
		$this->_items	=	$items;
		return $this;
	}

	/**
	 * Установка исключений
	 * @param array $items
	 * @return \Mappers\Router
	 */
	public function setExclude(array $exclude)
	{
		$this->_exclude	=	$exclude;
		return $this;
	}

	/**
	 * Отображаем каталог как json ответ
	 * @return \Mappers\Router
	 */
	public function json()
	{
		$this->_isJson	=	true;

		$this->view->disable();
		$this->response->setContentType('application/json', 'UTF-8');

		// отключаю лишние представления
		$this->view->disableLevel([

			View::LEVEL_LAYOUT 		=> true,
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
		switch($type['alias'])
		{
			case 'new':			// Новые

				$items	=	$this->_model->getProducts(
					$this->_shop['price_id'], [
						'prod.is_new = '	=>	'1',
						'price.price > '	=>	'0'
					], 0, $this->_limit, ['prod.id'], ['prod.date_income DESC'], true);

				break;

			case 'top':			// Топ 200

				// проверяю на фильтры gender
				$gender = end($this->_rules->catalogue);

				if($gender != 'top') {
					$this->_filter	=	[
						'prod.sex = ' 		=> $this->getGender($gender),
					];
					$this->setTitle(strtoupper($gender));
				}

				// гребу топ, устанавливаю макс. число для выдачи, тем самым отключаю для топа постраничный вывод
				$this->_limit	=	200;
				$items	=	$this->_model->getTopProducts($this->_shop['price_id'], $this->_filter, $this->_limit, true);

				break;

			case 'favorites':	// Добавленные в избранное

				$items	=	[];

				break;

			case 'sales':		// Распродажи

				// проверяю на фильтры gender
				$gender = end($this->_rules->catalogue);

				if($gender != 'sales') {

					$this->_filter	=	[
						'prod.sex ' 		=> [0, 3, $this->getGender($gender)],
						'price.discount > ' => '0',
					];

					if(!empty($this->_rules->query))
						$this->_filter[key($this->_rules->query).' = ']	=	$this->_rules->query[key($this->_rules->query)];

					$this->setTitle(strtoupper($gender));
				}

				// гребу выдачу

				$items	=	$this->_model->getProducts($this->_shop['price_id'], $this->_filter, 0, 100, ['prod.id'], ['(price.price/price.percent) DESC, prod.rating'], true);

				break;
			default :
			case 'top';
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

		if(!$this->_model)
			$this->setModel($model);

		// устанавливаю параметры магазина

		if(!$this->_shop)
			$this->setShop($shop);

		// устанавливаю правила, если их не задали

		if(!$this->_rules)
			$this->setRules($rules);

		// устанавливаю все категории с их описанием

		if(!$this->_collection)
			$this->setCollection($collection);

		// устанавливаю режим выдачи

		if($json == true)
			$this->setJson(true);

		// устанавливаю исключения (предусмотрена возможность если их и не будет)

		if(!$this->_exclude && !empty($exclude))
			$this->setExclude($exclude);

		// определяю категорию отображения
		// сначала проверяю ее в виртуальных, так как их меньше


		$intersect = array_intersect($this->_rules->catalogue, array_values(array_flip($this->_exclude)));

		if(isset($this->_exclude[$this->_rules->current])
			|| !empty($intersect))
		{
			// Обработка виртуалок

			$category	= [
				'name'	=>	$this->_exclude[$intersect[0]],
				'alias'	=>	$intersect[0]
			];

			// переключатель
			$items = $this->virtualSwitcher($category);

			// если есть в массиве счетчик удаляю его
			if(isset($items['count']))
				$count = array_pop($items);
		}
		else
		{
			// Обычная выдача категорий
			// определяю какой пол должен быть показан
			$this->setGender($this->_rules->catalogue);


			// 	ищем родителя категории, (уже сброшены, поэтому родитель всегда 0)
			$parent = Catalogue::findInTree($this->_collection, 'alias', $this->_rules->catalogue[0])[0];

			if($parent)
			{
				// поиск по полу и алиасу в массиве (findInTree2 продублировал)
				if($this->_gender > 0) // поиск с полом
					$category = Catalogue::findInTree2($this->_collection, 'alias', $this->_rules->catalogue[1], 'sex', $this->_gender)[0];
				else
					$category = Catalogue::findInTree($this->_collection, 'alias', $this->_rules->catalogue[1])[0];

				// непутевая ситуация... если нам выдало,
				// что категория для выборки товара у нас называется как пол человека, мы должны теперь найти ее parent_id
				// и узнать реальную картину, снова поискать в дереве и пересобрать

				if($this->getGender($this->_rules->catalogue[1]))
				{
					// узнаем ID родителя
					$parent = Catalogue::findInTree($this->_collection, 'alias', $this->_rules->catalogue[0])[0];
					// пол нам известен, id родителя тоже... теперь найдем id реальной категории и вперед на поиск

					$category = Catalogue::findInTree2($this->_collection, 'parent_id', $parent['id'], 'sex', $this->_gender)[0];
				}

				// добавляю в навигацию
				$this->_breadcrumbs->add($parent['name'], 'catalogue/'.$parent['alias']);

				//@todo Будет проверка на фильтры

				$items	=	$this->_model->getProducts(
					$this->_shop['price_id'], [														// WHERE
						'rel.category_id  = '	=>	$category['id'],
						'prod.sex = '			=>	($this->_gender > 0) ? $this->_gender : false,
					],
					$this->_rules->query, true);

				// сохраняю связь чтобы не дергать из MySQL. Записываю параметры текущей категории в сессию
				// она каждый раз перезаписывается по мере новой загрузки категории, поэтому не стоит волноваться
				// это будет главный ключ используемый для связи тегов и категорий
				// {id:31848, parent_id:394, sex:0, name:Браслеты, alias:bracelet, sort:0, description:}
				$this->session->set('category', $category);

				$count = array_pop($items);

				// постраничная навигация
				if($count > $this->_limit)
				{
					$current = $this->request->getQuery("page");
					$this->_pagination	=	[
						'limit'		=>	$this->_limit,
						'offset'	=>	$this->_limit,
						'current'	=>	($current) ? $current : 1,
						'count'		=>	$count,
						'pages'		=>	floor($count/$this->_limit)
					];
				}
			}
			else // не найдена такая категория вообще
				return $this->view->render('error', 'show404')->pick("error/show404");
		}

		// задаю шаблон для вывода результата выдачи
		$this->setItems($items)->setTemplate('itemsline');

		// Устанавливаю мета данные
		$this->setTitle($category['name']);
		$this->_breadcrumbs->add($category['name'], 'catalogue/'.$category['alias']);


		$templateVars	=	[
			'title'			=>	$this->_title,
			'template'  	=> 	$this->_template,
			'items'			=>	$this->_items,
			'pagination'	=>	$this->_pagination,
			'query'			=>	$this->_rules->query
		];

		if($this->_isJson)
		{
			// создаю Json контекст

			$this->response->setJsonContent([
				'response'	=>	$this->view->getRender('partials/catalogue', $this->_template, $templateVars)
			]);

			// отправляю ответ
			$this->response->send();
		}
		else
		{
			// передача переменных в шаблон
			$this->view->setVars($templateVars);

			// рендеринг шаблона
			$this->view->pick("catalogue/index");
		}
	}
}
