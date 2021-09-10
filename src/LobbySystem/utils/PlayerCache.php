<?php

namespace LobbySystem\utils;

use LobbySystem\Loader;
use LobbySystem\party\PartyManager;
use pocketmine\scheduler\ClosureTask;

class PlayerCache
{
	/**
	 * @var string[]
	 */
	private static $cache = [];

	/**
	 * @param string[] $players
	 */
	public static function set(array $players): void
	{
		foreach (array_diff($players, self::$cache) as $player) {
			if (in_array($player, $players, true)) {
				$party = PartyManager::get($player);
				if (isset($party->offline[$player])) {
					$party->offline[$player]->cancel();
					unset($party->offline[$player]);
				}
			} else {
				$party = PartyManager::get($player);
				if ($party->isValid()) {
					$party->offline[$player] = Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(static function () use ($party, $player): void {
						$party->remove($player);
					}), 6000);
				}
			}
		}
		self::$cache = array_combine(array_map(static function (string $p): string { return strtolower($p); }, $players), $players);
	}

	/**
	 * @param string $player
	 * @return bool
	 */
	public static function isOnline(string $player): bool
	{
		return isset(self::$cache[strtolower($player)]);
	}

	/**
	 * @param string $player
	 * @return string
	 */
	public static function get(string $player): string
	{
		return self::$cache[strtolower($player)] ?? $player;
	}

	/**
	 * @param string[] $data
	 * @return string[]
	 */
	public static function getRecursive(array $data): array
	{
		foreach ($data as $i => $dat) {
			$data[$i] = self::get($dat);
		}
		return $data;
	}

	/**
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	public static function getRecursiveKeys(array $data): array
	{
		foreach ($data as $dat => $i) {
			$data[self::get($dat)] = $i;
		}
		return $data;
	}
}