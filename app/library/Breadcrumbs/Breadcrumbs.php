<?php
namespace Breadcrumbs;


class Breadcrumbs {

	/**
	 * Набор для хранения элементов
	 * @var
	 */
	private $_elements;

	/**
	 * Принимаю по умолчанию параметры главной страницы
	 */
	public function __construct()
	{
		// Ставлю началом в цепочке главную страницу

		$this->_elements[] = [
			'active' => false,
			'link'   => '/',
			'text'   => 'Главная',
		];
	}

	/**
	 * Добавление элементов в цепочку
	 *
	 * @param string $caption заголовок
	 * @param string $link ссылка
	 */
	public function add($caption, $link)
	{
		$this->_elements[] = [
			'active' => false,
			'link'   => '/' . $link,
			'text'   => $caption,
		];
	}

	/**
	 * Сброс цепочки
	 */
	public function reset()
	{
		$this->_elements = [];
	}

	/**
	 * Генерация JSON из элементов навигации
	 *
	 * @return string
	 */
	public function generate()
	{
		$lastKey = key(array_slice($this->_elements, -1, 1, true));
		$this->_elements[$lastKey]['active'] = true;
		return $this->_elements;
	}
}