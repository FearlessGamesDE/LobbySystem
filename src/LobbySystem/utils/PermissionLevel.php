<?php

namespace LobbySystem\utils;

class PermissionLevel
{
	public const ADMINISTRATOR = 99;
	public const MODERATOR = 80;
	public const DEFAULT = 0;

	/**
	 * @param string $player
	 * @param int    $neededLevel
	 * @return bool
	 */
	public static function canUse(string $player, int $neededLevel): bool
	{
		return InternalInformation::getPermissionLevel($player) >= $neededLevel;
	}
}