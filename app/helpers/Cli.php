<?php
namespace Helpers;
use Phalcon\Tag;

/**
 * Class Cli Помощник для работы с коммандной строкой
 * @package Phalcon
 * @subpackage Helpers
 */
class Cli extends  Tag
{
	public static function colorize($string, $status) {
		switch($status)
		{
			case "SUCCESS":
				$out = "[42m"; // Green background
				break;
			case "FAILURE":
				$out = "[41m"; // Red background
				break;
			case "WARNING":
				$out = "[43m"; // Yellow background
				break;
			case "NOTE":
				$out = "[44m"; // Blue background
				break;
			default:
				throw new \Exception("Invalid status: " . $status);
		}
		return chr(27) . "$out" . "$string" . chr(27) . "[0m\r\n";
	}

	public static function bold($string)
	{
		return "\033[1m".$string."\033[0m";
	}
}