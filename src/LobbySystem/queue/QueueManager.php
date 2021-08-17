<?php

namespace LobbySystem\queue;

use LobbySystem\gamemode\FreeGamemode;
use LobbySystem\gamemode\Gamemode;
use LobbySystem\Loader;
use LobbySystem\party\PartyManager;
use LobbySystem\utils\PlayerCache;
use LobbySystem\utils\StarGateUtil;
use LobbySystem\utils\Output;
use pocketmine\player\Player;
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
	 * @param Player   $player
	 * @param Gamemode $gamemode
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
		foreach ($party->getContents() as $p) {
			if (($pl = Server::getInstance()->getPlayerExact($p)) instanceof Player) {
				$old = self::getQueueOf($pl);
				if ($old instanceof Queue) {
					$old->remove($pl);
				}
			} elseif (PlayerCache::isOnline($p)) {
				StarGateUtil::transferPlayer($p, "lobby");
				Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(static function () use ($player, $gamemode): void {
					self::add($player, $gamemode);
				}), 40);
				return;
			} else {
				Output::send($player, "party-offline", ["{player}" => $p], "party-prefix");
				return;
			}
		}
		$queue = $gamemode->getQueue($party->getSize());
		foreach ($party->getContents() as $pla) {
			$play = Server::getInstance()->getPlayerExact($pla);
			if ($play instanceof Player) {
				$queue->add($play);
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
	 * @param int $id
	 * @return Queue
	 */
	public static function get(int $id): Queue
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