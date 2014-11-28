<?php
/**
 *  Автозагрузчик для модуля ZKZ
 */
namespace Modules\Module;

use Phalcon\Loader,
	Phalcon\Mvc\ModuleDefinitionInterface;

/**
 * Class ZKZ
 * @package Phalcon
 * @subpackage Modules\ZKZ
 */
class ZKZ implements ModuleDefinitionInterface
{
	/**
	 * Код модуля
	 */
	const MODULE	=	'ZKZ';

	/**
	 * Настройки модуля
	 * @var bool | array
	 */
	private $_config = false;

	/**
	 * Инициализация конфига модуля
	 */
	public function __construct() {

		// Подключение настроек модуля
		require APP_PATH.'/modules/'.self::MODULE.'/config/config.php';
		$this->_config = (isset($config)) ? $config : false;
	}

	/**
	 * Регистрация автозагрузчика, специфичного для текущего модуля
	 */
	public function registerAutoloaders()
	{
		$loader = new Loader();

		$loader->registerNamespaces([
			'Modules\\'.self::MODULE.'\Controllers' 	=> 	$this->_config['application']['controllersDir'],
			'Models'      				=> 	$this->_config['application']['modelsDir'],
			'Helpers'      				=> 	$this->_config['application']['helpersDir'],
			'Mappers'					=>	$this->_config['application']['mappersDir'],
		])
		->registerDirs([
			APP_PATH.'/library/',
		])
		->register();
	}

	/**
	 * Регистрация специфичных сервисов для модуля
	 */
	public function registerServices($di)
	{
		return require APP_PATH.'/modules/'.self::MODULE.'/config/di.php';
	}
}