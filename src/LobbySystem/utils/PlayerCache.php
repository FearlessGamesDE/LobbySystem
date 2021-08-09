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
	 * @param $data
	 * @return array|string
	 */
	public static function getRecursive($data)
	{
		if(is_array($data)){
			foreach ($data as $i => $dat){
				$data[$i] = self::getRecursive($dat);
			}
			return $data;
		}

		return self::get($data);
	}
}