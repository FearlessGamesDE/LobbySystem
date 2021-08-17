<?php

namespace LobbySystem\gamemode\minigame;

use LobbySystem\Loader;
use RuntimeException;

class MinigameManager
{
	/**
	 * @var Minigame[]
	 */
	private static $minigames = [];

	public static function load(): void
	{
		if (!is_dir(Loader::getInstance()->getDataFolder() . "minigames") && !mkdir($concurrentDirectory = Loader::getInstance()->getDataFolder() . "minigames") && !is_dir($concurrentDirectory)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
		/** @var string $file */
		foreach (array_diff((array) scandir(Loader::getInstance()->getDataFolder() . "minigames"), [".", ".."]) as $file) {
			$data = yaml_parse_file(Loader::getInstance()->getDataFolder() . "minigames/" . $file);
			self::registerMinigame(new Minigame($file, $data["name"] ?? $file));
		}
	}

	/**
	 * @param Minigame $minigame
	 */
	public static function registerMinigame(Minigame $minigame): void
	{
		self::$minigames[$minigame->getId()] = $minigame;
	}

	/**
	 * @param string $id
	 * @return Minigame|null
	 */
	public static function getMinigame(string $id): ?Minigame
	{
		return self::$minigames[$id] ?? null;
	}

	/**
	 * @return Minigame[]
	 */
	public static function getMinigames(): array
	{
		return array_values(self::$minigames);
	}
}