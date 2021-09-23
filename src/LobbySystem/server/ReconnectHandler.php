<?php

namespace LobbySystem\server;

use pocketmine\player\Player;

class ReconnectHandler
{
	/**
	 * @var array<string, array{string, int}>
	 */
	private static array $locations = [];

	/**
	 * @param string $player
	 * @param string $server
	 */
	public static function setLocation(string $player, string $server): void
	{
		self::$locations[strtolower($player)] = [$server, time()];
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public static function hasLocation(Player $player): bool
	{
		if (isset(self::$locations[strtolower($player->getName())])) {
			return (self::$locations[strtolower($player->getName())][1] + 300) > time() && (self::$locations[strtolower($player->getName())][1] + 5) < time();
		}
		return false;
	}

	/**
	 * @param Player $player
	 * @return string
	 */
	public static function getLocation(Player $player): string
	{
		if (isset(self::$locations[strtolower($player->getName())])) {
			return self::$locations[strtolower($player->getName())][0];
		}
		return "lobby";
	}
}