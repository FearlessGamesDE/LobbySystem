<?php

namespace LobbySystem\party;

class PartyManager
{
	/**
	 * @var Party[]
	 */
	private static $parties = [];

	/**
	 * @param string $player
	 * @return Party
	 */
	public static function get(string $player): Party
	{
		foreach (self::$parties as $party) {
			if($party->contains($player)){
				return $party;
			}
		}
		return new Party($player);
	}

	/**
	 * @param Party $party
	 */
	public static function add(Party $party): void
	{
		self::$parties[$party->getOwner()] = $party;
	}

	/**
	 * @param Party $party
	 */
	public static function remove(Party $party): void
	{
		unset(self::$parties[$party->getOwner()]);
	}
}