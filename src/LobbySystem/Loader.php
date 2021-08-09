<?php

namespace LobbySystem;

use alemiz\sga\StarGateAtlantis;
use LobbySystem\command\CommandManager;
use LobbySystem\gamemode\GamemodeManager;
use LobbySystem\packets\server\DisablePacket;
use LobbySystem\packets\server\EnablePacket;
use LobbySystem\packets\PacketHandler;
use LobbySystem\queue\QueueManager;
use LobbySystem\utils\ErrorReporter;
use LobbySystem\utils\Output;
use LobbySystem\utils\RawLogger;
use LobbySystem\utils\TimingManager;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class Loader extends PluginBase
{
	public const MESSAGES_VERSION = 1;
	/**
	 * @var Loader
	 */
	public static $instance;

	public static $serverName = "";

	public function onEnable(): void
	{
		self::$instance = $this;
		self::$serverName = StarGateAtlantis::getInstance()->getClientName();
		TimingManager::load();
		Output::load();
		PacketHandler::load();
		CommandManager::load();
		ErrorReporter::load();
		Server::getInstance()->getPluginManager()->registerEvents(new RawLogger(), $this);
		if(StarGateAtlantis::getInstance()->getClientName() === "lobby"){
			GamemodeManager::load();
			QueueManager::load();
			StarGateAtlantis::getInstance()->forwardPacket("all", "default", new EnablePacket());
		}
	}

	public function onDisable(): void
	{
		StarGateAtlantis::getInstance()->forwardPacket("all", "default", new DisablePacket());
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
}