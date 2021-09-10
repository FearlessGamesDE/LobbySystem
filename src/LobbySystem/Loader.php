<?php

namespace LobbySystem;

use LobbySystem\area\AreaManager;
use LobbySystem\command\CommandManager;
use LobbySystem\gamemode\GamemodeManager;
use LobbySystem\packets\server\DisablePacket;
use LobbySystem\packets\PacketHandler;
use LobbySystem\packets\server\ReadyPacket;
use LobbySystem\queue\QueueManager;
use LobbySystem\utils\ErrorReporter;
use LobbySystem\utils\InternalInformation;
use LobbySystem\utils\Output;
use LobbySystem\utils\PlayerHandler;
use LobbySystem\utils\RawLogger;
use LobbySystem\utils\SecurityChecks;
use LobbySystem\utils\StarGateUtil;
use LobbySystem\utils\TimingManager;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class Loader extends PluginBase
{
	public const MESSAGES_VERSION = 1;
	/**
	 * @var Loader
	 */
	private static $instance;
	/**
	 * @var bool
	 */
	private static $isMaster;
	/**
	 * @var string
	 */
	private static $serverName = "";

	public function onEnable(): void
	{
		self::$instance = $this;
		self::$serverName = StarGateUtil::getClient()->getClientName();
		self::$isMaster = self::$serverName === "lobby";
		SecurityChecks::checkCompressionStatus();
		TimingManager::load();
		Output::load();
		PacketHandler::load();
		CommandManager::load();
		ErrorReporter::load();
		InternalInformation::init();
		Server::getInstance()->getPluginManager()->registerEvents(new RawLogger(), $this);
		Server::getInstance()->getPluginManager()->registerEvents(new PlayerHandler(), $this);
		if (self::$isMaster) {
			GamemodeManager::load();
			QueueManager::load();
			AreaManager::load();

			$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(static function (): void {
				StarGateUtil::refreshServerInformation();
			}), 40);
		}
	}

	public function onDisable(): void
	{
		StarGateUtil::distribute(new DisablePacket());
		TimingManager::send();
		ErrorReporter::send();
		$this->getServer()->shutdown();
	}

	/**
	 * @return Loader
	 */
	public static function getInstance(): Loader
	{
		return self::$instance;
	}

	/**
	 * @return string
	 */
	public static function getServerName(): string
	{
		return self::$serverName;
	}

	/**
	 * @return bool
	 */
	public static function isMaster(): bool
	{
		return self::$isMaster;
	}
}