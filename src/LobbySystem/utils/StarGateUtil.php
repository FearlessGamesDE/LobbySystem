<?php

namespace LobbySystem\utils;

use alemiz\sga\client\StarGateClient;
use alemiz\sga\protocol\ForwardPacket;
use alemiz\sga\protocol\ManageServerPacket;
use alemiz\sga\protocol\ServerInfoResponsePacket;
use alemiz\sga\protocol\ServerTransferPacket;
use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\StarGateAtlantis;
use Exception;
use LobbySystem\Loader;
use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketHandler;
use LobbySystem\packets\server\EnablePacket;
use UnexpectedValueException;

class StarGateUtil
{
	/**
	 * @var string[]
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
		$client = StarGateAtlantis::getInstance()->getDefaultClient();
		if ($client === null) {
			throw new UnexpectedValueException("No default client found");
		}
		return $client;
	}

	/**
	 * @param NetworkPacket $packet
	 */
	public static function request(NetworkPacket $packet): void
	{
		if (Loader::isMaster()) {
			//TODO: simplify this
			$packet->encodePayload();
			$packet->decodePayload();
			PacketHandler::handle($packet);
		} else {
			self::sendTo("lobby", $packet);
		}
	}

	/**
	 * @param string         $server
	 * @param StarGatePacket $packet
	 */
	public static function sendTo(string $server, StarGatePacket $packet): void
	{
		self::getClient()->sendPacket(ForwardPacket::from($server, $packet));
	}

	/**
	 * @param StarGatePacket $packet
	 */
	public static function distribute(StarGatePacket $packet): void
	{
		foreach (self::$servers as $server) {
			self::sendTo($server, $packet);
		}
	}

	/**
	 * @param string[] $servers
	 */
	public static function serverListCallback(array $servers): void
	{
		self::$servers = $servers;
		if (!self::$init && Loader::isMaster()) {
			self::$init = true;
			self::distribute(new EnablePacket());
		}
	}

	public static function refreshServerInformation(): void
	{
		$response = StarGateAtlantis::getInstance()->serverInfo(Loader::getServerName(), true);
		if ($response !== null) {
			$response->whenComplete(static function ($response, ?Exception $e): void {
				if ($response instanceof ServerInfoResponsePacket) {
					self::serverListCallback($response->getServerList());
					PlayerCache::set($response->getPlayerList());
				}
			});
		}
	}

	/**
	 * @param string $player
	 * @param string $server
	 */
	public static function transferPlayer(string $player, string $server): void
	{
		$packet = new ServerTransferPacket();
		$packet->setPlayerName($player);
		$packet->setTargetServer($server);
		self::getClient()->sendPacket($packet);
	}

	/**
	 * @param string      $serverName
	 * @param int         $port
	 * @param string|null $ip
	 */
	public static function addServer(string $serverName, int $port, string $ip = null): void
	{
		$packet = new ManageServerPacket();
		$packet->action = ManageServerPacket::ADD;
		$packet->serverName = $serverName;
		$packet->address = $ip ?? "0.0.0.0";
		$packet->addressPort = $port;
		$packet->serverType = "bedrock";
		self::getClient()->sendPacket($packet);
	}

	/**
	 * @param string $serverName
	 */
	public static function removeServer(string $serverName): void
	{
		$packet = new ManageServerPacket();
		$packet->action = ManageServerPacket::REMOVE;
		$packet->serverName = $serverName;
		self::getClient()->sendPacket($packet);
	}
}