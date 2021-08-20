<?php

namespace LobbySystem\server;

use LobbySystem\packets\server\InitializePacket;
use UnexpectedValueException;

abstract class VirtualServer
{
	/**
	 * @var bool
	 */
	private static $init = false;
	/**
	 * @var string
	 */
	private static $gamemodeId;
	/**
	 * @var string
	 */
	private static $minigame;
	/**
	 * @var int
	 */
	private static $teamCount = 0;
	/**
	 * @var int
	 */
	private static $teamSize = 0;
	/**
	 * @var string[]
	 */
	private static $players = [];
	/**
	 * @var string[][]
	 */
	private static $teams = [];

	/**
	 * @var VirtualServer
	 */
	private static $instance;

	/**
	 * @param VirtualServer $server
	 */
	public static function register(VirtualServer $server): void
	{
		self::$instance = $server;
	}

	/**
	 * @param InitializePacket $packet
	 */
	public static function init(InitializePacket $packet): void
	{
		if (self::$init) {
			throw new UnexpectedValueException("Cannot init already init server");
		}
		self::$gamemodeId = $packet->gamemodeId;
		self::$minigame = $packet->minigame;
		self::$teamCount = $packet->teamCount;
		self::$teamSize = $packet->teamSize;
		self::$players = $packet->players;
		self::$teams = $packet->teams;
		self::$instance->onInit();
	}

	abstract public function onInit(): void;

	/**
	 * @return string
	 */
	public static function getGamemodeId(): string
	{
		return self::$gamemodeId;
	}

	/**
	 * @return string
	 */
	public static function getMinigame(): string
	{
		return self::$minigame;
	}

	/**
	 * @return int
	 */
	public static function getTeamCount(): int
	{
		return self::$teamCount;
	}

	/**
	 * @return int
	 */
	public static function getTeamSize(): int
	{
		return self::$teamSize;
	}

	/**
	 * @return string[]
	 */
	public static function getRegisteredPlayers(): array
	{
		return self::$players;
	}

	/**
	 * @return string[][]
	 */
	public static function getTeams(): array
	{
		return self::$teams;
	}

	/**
	 * @return VirtualServer
	 */
	public static function getInstance(): VirtualServer
	{
		return self::$instance;
	}
}