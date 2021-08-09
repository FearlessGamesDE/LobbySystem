<?php

namespace LobbySystem\utils;

use pocketmine\level\Level;
use pocketmine\Server;

class LobbyLevel
{
	/**
	 * be quiet PhpStorm
	 * @return Level
	 */
	public static function get(): Level
	{
		return Server::getInstance()->getDefaultLevel();
	}
}