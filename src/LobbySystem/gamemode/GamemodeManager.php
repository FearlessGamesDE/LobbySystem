<?php

namespace LobbySystem\gamemode;

use LobbySystem\gamemode\minigame\Minigame;
use LobbySystem\gamemode\minigame\MinigameManager;
use LobbySystem\Loader;
use pocketmine\Server;
use RuntimeException;

class GamemodeManager
{
	/**
	 * @var Gamemode[]
	 */
	private static $gamemodes = [];

	public static function load(): void
	{
		MinigameManager::load();
		if (!is_dir(Loader::getInstance()->getDataFolder() . "gamemodes") && !mkdir($concurrentDirectory = Loader::getInstance()->getDataFolder() . "gamemodes") && !is_dir($concurrentDirectory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
		/** @var string $file */
		foreach (array_diff((array) scandir(Loader::getInstance()->getDataFolder() . "gamemodes"), [".", ".."]) as $file) {
			$data = yaml_parse_file(Loader::getInstance()->getDataFolder() . "gamemodes/" . $file);
			$minigame = MinigameManager::getMinigame($data["game"] ?? "");
			if (!$minigame instanceof Minigame) {
				Server::getInstance()->getLogger()->critical("Unknown Minigame " . $data["game"] ?? "undefined");
				continue;
			}
			switch ($data["type"] ?? "default") {
				case "default":
					self::registerGamemode(new Gamemode($minigame, $data["name"] ?? $file, $data["max"] ?? 2, $data["min"] ?? 2));
					break;
				case "team":
					self::registerGamemode(new TeamGamemode($minigame, $data["name"] ?? $file, $data["teamCount"] ?? 2, $data["teamSize"] ?? 1));
					break;
				case "free":
					self::registerGamemode(new FreeGamemode(new Minigame(strtolower($file), $data["name"] ?? $file), $data["name"] ?? $file));
					break;
				case "ffa":
					self::registerGamemode(new FFAGamemode(new Minigame(strtolower($file), $data["name"] ?? $file), $data["name"] ?? $file));
					break;
				default:
					Server::getInstance()->getLogger()->critical("Unknown Gametype " . $data["type"]);
			}
		}
	}

	/**
	 * @param Gamemode $gamemode
	 */
	public static function registerGamemode(Gamemode $gamemode): void
	{
		self::$gamemodes[$gamemode->getId()] = $gamemode;
	}

	/**
	 * @param string $id
	 * @return Gamemode
	 */
	public static function getGamemode(string $id): Gamemode
	{
		return self::$gamemodes[$id];
	}

	/**
	 * @return Gamemode[]
	 */
	public static function getGamemodes(): array
	{
		return self::$gamemodes;
	}
}