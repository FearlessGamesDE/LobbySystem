<?php

namespace LobbySystem\queue;

use alemiz\sga\StarGateAtlantis;
use LobbySystem\gamemode\FreeGamemode;
use LobbySystem\Gamemode\Gamemode;
use LobbySystem\Loader;
use LobbySystem\party\PartyManager;
use LobbySystem\utils\Output;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class QueueManager
{
	/**
	 * @var Queue[]
	 */
	private static $queues = [];

	public static function load(): void
	{
		Server::getInstance()->getPluginManager()->registerEvents(new QueueHandler(), Loader::getInstance());
	}
	
	/**
	 * @param Player $player
	 * @param Gamemode $gamemode
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function add(Player $player, Gamemode $gamemode): void
	{
		if ($gamemode instanceof FreeGamemode) {
			$gamemode->getQueue()->add($player);
			return;
		}
		$party = PartyManager::get($player->getName());
		if ($party->getOwner() !== strtolower($player->getName())) {
			Output::send($player, "party-nopermission-owner", [], "party-prefix");
			return;
		}
		if ($party->getSize() > $gamemode->getCapacity()) {
			Output::send($player, "party-to-big", [], "party-prefix");
			return;
		}
		$players = $party->getSize();
		foreach ($party->getContents() as $p) {
			if (($pl = Server::getInstance()->getPlayerExact($p)) instanceof Player) {
				$old = self::getQueueOf($pl);
				if ($old instanceof Queue) {
					$old->remove($pl);
				}
				$players--;
				if ($players === 0) {
					$queue = $gamemode->getQueue($party->getSize());
					if ($queue instanceof Queue) {
						foreach ($party->getContents() as $pla) {
							$queue->add(Server::getInstance()->getPlayerExact($pla));
						}
					}
				}
			} else {
				StarGateAtlantis::getInstance()->isOnline($p, static function (string $response) use (&$players, $player, $p, $gamemode) {
					if ($response === "false") {
						Output::send($player, "party-offline", ["{player}" => $p], "party-prefix");
					} else {
						$players--;
						StarGateAtlantis::getInstance()->transferPlayer($p, "lobby");
						if ($players === 0) {
							Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(static function (int $currentTick) use ($player, $gamemode) : void {
								self::add($player, $gamemode);
							}), 20 * 5);
						}
					}
				});
			}
		}
	}

	/**
	 * @param Player $player
	 */
	public static function remove(Player $player): void
	{
		$queue = self::getQueueOf($player);
		if ($queue instanceof Queue) {
			$queue->remove($player);
		}
	}

	/**
	 * @param Player $player
	 * @return Queue|null
	 */
	public static function getQueueOf(Player $player): ?Queue
	{
		foreach (self::$queues as $queue) {
			if ($queue->contains($player)) {
				return $queue;
			}
		}
		return null;
	}

	/**
	 * @param string $id
	 * @return Queue
	 */
	public static function get(string $id): Queue
	{
		return self::$queues[$id];
	}

	/**
	 * @param Gamemode $gamemode
	 */
	public static function open(Gamemode $gamemode): void
	{
		$queue = new Queue($gamemode);
		$gamemode->addQueue($queue);
		self::$queues[$queue->getId()] = $queue;
	}

	/**
	 * @param Queue $queue
	 */
	public static function unbind(Queue $queue): void
	{
		unset(self::$queues[$queue->getId()]);
		$queue->getGamemode()->removeQueue($queue);
	}
}