<?php

namespace LobbySystem\utils;

use alemiz\sga\client\StarGateClient;
use alemiz\sga\protocol\ForwardPacket;
use alemiz\sga\protocol\ServerInfoResponsePacket;
use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\StarGateAtlantis;
use LobbySystem\Loader;
use LobbySystem\packets\server\EnablePacket;
use pocketmine\Server;

class StarGateUtil
{
	/**
	 * @var array
	 */
	private static $servers = [];
	/**
	 * @var bool
	 */
	private static $init = false;

	/**
	 * @return StarGateClient
	 */
	public static function getClient(): StarGateClient
	{
		return StarGateAtlantis::getInstance()->getDefaultClient();
	}

	/**
	 * @param StarGatePacket $packet
	 */
	public static function distribute(StarGatePacket $packet): void
	{
		foreach (self::$servers as $server) {
			self::getClient()->sendPacket(ForwardPacket::from($server, $packet));
		}
	}

	/**
	 * @param array $servers
	 */
	public static function serverListCallback(array $servers): void
	{
		self::$servers = $servers;
		if (!self::$init) {
			self::$init = true;
			self::distribute(new EnablePacket());
		}
	}

	public static function refreshServerList(): void
	{
		$response = StarGateAtlantis::getInstance()->serverInfo(Loader::getServerName(), true);
		if ($response !== null) {
			$response->whenComplete(static function ($response, \Exception $e) {
				if ($response instanceof ServerInfoResponsePacket) {
					self::serverListCallback($response->getServerList());
				}
			});
		}
	}
}