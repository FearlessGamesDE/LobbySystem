<?php

namespace LobbySystem\utils;

use pocketmine\Server;
use pocketmine\world\World;
use UnexpectedValueException;

class LobbyWorld
{
	/**
	 * be quiet PhpStorm
	 * @return World
	 */
	public static function get(): World
	{
		$world = Server::getInstance()->getWorldManager()->getDefaultWorld();
		if($world instanceof World){
			return $world;
		}
		throw new UnexpectedValueException("Lobby world not found");
	}
}