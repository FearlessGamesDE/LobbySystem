<?php

namespace LobbySystem\utils;

use platz1de\StatAPI\Module;
use platz1de\StatAPI\Stat;

class InternalInformation
{
	private const PLAYER_MODULE_NAME = "internal_player_information";
	private const PLAYER_PERMISSION_LEVEL_NAME = "internal_permission_level";

	private const CONVERSION_KEYS = "internal_conversion_keys";
	private const PERMISSION_LEVEL_TO_RANK_NAME = "internal_level_to_rank";
	private const PERMISSION_LEVEL_TO_CHAT_PREFIX = "internal_level_to_chat";

	private static Module $players;
	private static Stat $permissionLevel;

	private static Module $conversionKeys;
	private static Stat $permissionToRank;
	private static Stat $permissionToChat;

	public static function init(): void
	{
		//PLAYER DATA

		self::$players = Module::get(self::PLAYER_MODULE_NAME);
		self::$players->setVisible(false);

		self::$permissionLevel = Stat::get(self::PLAYER_PERMISSION_LEVEL_NAME, self::$players);
		self::$permissionLevel->setVisible(false);

		//CONVERSION KEYS

		self::$conversionKeys = Module::get(self::CONVERSION_KEYS);
		self::$conversionKeys->setVisible(false);

		self::$permissionToRank = Stat::get(self::PERMISSION_LEVEL_TO_RANK_NAME, self::$conversionKeys);
		self::$permissionToRank->setVisible(false);
		self::$permissionToRank->setDefault("Unknown");

		self::$permissionToChat = Stat::get(self::PERMISSION_LEVEL_TO_CHAT_PREFIX, self::$conversionKeys);
		self::$permissionToChat->setVisible(false);
		self::$permissionToChat->setDefault("§8[???] §r");
	}

	private const PROFILE = "profile";
	private const RANK = "rank";

	/**
	 * @param string $player
	 */
	public static function constructProfileData(string $player): void
	{
		$profile = Module::get(self::PROFILE);
		$profile->setDisplayName("Profile");

		$rank = Stat::get(self::RANK, $profile);
		$rank->setDisplayName("Rank");
		$rank->setScore($player, self::getRankName($player), false);
	}

	/**
	 * @param string $player
	 * @return int
	 */
	public static function getPermissionLevel(string $player): int
	{
		return (int) self::$permissionLevel->getScore($player);
	}

	/**
	 * @param string $player
	 * @param int    $permissionLevel
	 */
	public static function setPermissionLevel(string $player, int $permissionLevel): void
	{
		self::$permissionLevel->setScore($player, (string) $permissionLevel);
	}

	/**
	 * @param string $player
	 * @return int
	 */
	public static function getRankName(string $player): string
	{
		return self::$permissionToRank->getScore(self::getPermissionLevel($player));
	}

	/**
	 * @param string $player
	 * @return string
	 */
	public static function getChatPrefix(string $player): string
	{
		return self::$permissionToChat->getScore(self::getPermissionLevel($player));
	}
}