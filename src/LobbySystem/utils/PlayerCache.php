<?php

namespace LobbySystem\utils;

class PlayerCache
{
	/**
	 * @var string[]
	 */
	private static $cache = [];

	/**
	 * @param string $player
	 */
	public static function add(string $player): void
	{
		self::$cache[strtolower($player)] = $player;
	}

	/**
	 * @param string $player
	 */
	public static function remove(string $player): void
	{
		unset(self::$cache[strtolower($player)]);
	}

	/**
	 * @param string $player
	 * @return bool
	 */
	public static function isKnown(string $player): bool
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