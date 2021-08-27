<?php

namespace LobbySystem\queue;

use alemiz\sga\StarGateAtlantis;
use LobbySystem\gamemode\Gamemode;
use LobbySystem\gamemode\TeamGamemode;
use LobbySystem\Loader;
use LobbySystem\packets\server\InitializePacket;
use LobbySystem\party\PartyManager;
use LobbySystem\server\ServerPool;
use LobbySystem\server\ServerPoolEntry;
use LobbySystem\utils\Generator;
use LobbySystem\utils\Output;
use LobbySystem\utils\StarGateUtil;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;

class Queue
{
	/**
	 * @var int
	 */
	private $id;
	/**
	 * @var Gamemode
	 */
	private $gamemode;
	/**
	 * @var Player[]
	 */
	protected $players = [];
	/**
	 * @var int
	 */
	public $tick = 60;
	/**
	 * @var TaskHandler
	 */
	private $ticker;
	/**
	 * @var ServerPoolEntry
	 */
	protected $server;
	/**
	 * @var bool
	 */
	protected $ready = false;

	/**
	 * Queue constructor.
	 * @param Gamemode $gamemode
	 */
	public function __construct(Gamemode $gamemode)
	{
		$this->gamemode = $gamemode;
		$this->id = Generator::generateQueueId();
	}

	/**
	 * @param Player $player
	 */
	public function add(Player $player): void
	{
		Output::send($this->players, "queue-join-other", ["{player}" => $player->getName(), "{current}" => (string) (count($this->players) + 1), "{max}" => (string) $this->gamemode->getCapacity()]);
		$this->players[$player->getName()] = $player;
		Output::send($player, "queue-join", ["{game}" => $this->gamemode->getDisplayName(), "{current}" => (string) count($this->players), "{max}" => (string) $this->gamemode->getCapacity()]);
		if ($this->tick > 10 && count($this->players) === $this->gamemode->getCapacity()) {
			QueueManager::unbind($this);
			$this->startServer();
		} elseif (count($this->players) === $this->gamemode->getMinimum()) {
			$this->tick = 60;
			$this->ticker = Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
				$this->tick();
			}), 20);
		}
	}

	/**
	 * @param Player $player
	 */
	public function remove(Player $player): void
	{
		Output::send($player, "queue-leave", ["{game}" => $this->gamemode->getDisplayName()]);
		unset($this->players[$player->getName()]);
		Output::send($this->players, "queue-leave-other", ["{player}" => $player->getName(), "{current}" => (string) count($this->players), "{max}" => (string) $this->gamemode->getCapacity()]);
		if (count($this->players) < $this->gamemode->getMinimum()) {
			$this->stopServer();
			if (isset($this->ticker)) {
				$this->ticker->cancel();
			}
			if ($this->gamemode->getQueue() !== $this) {
				foreach ($this->players as $p) {
					if (!$p->isClosed()) {
						QueueManager::add($p, $this->gamemode);
					}
				}
			}
		}
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function contains(Player $player): bool
	{
		return isset($this->players[$player->getName()]);
	}

	/**
	 * @return Gamemode
	 */
	public function getGamemode(): Gamemode
	{
		return $this->gamemode;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getSize(): int
	{
		return count($this->players);
	}

	public function tick(): void
	{
		//TODO: Test...
		/**
		 * @noinspection PhpExpressionResultUnusedInspection
		 * @phpstan-ignore-next-line
		 */
		$_ENV; //why????
		switch ($this->tick) {
			case 60:
			case 30:
			case 20:
			case 10:
			case 5:
			case 4:
			case 3:
			case 2:
			case 1:
				Output::send($this->players, "start-in", ["{seconds}" => (string) $this->tick]);
				break;
			case 11:
				$this->startServer();
				break;
			case 0:
				$this->teleport();
		}
		$this->tick--;
	}

	public function startServer(): void
	{
		$this->tick = 11;
		if (!isset($this->ticker)) {
			$this->ticker = Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
				$this->tick();
			}), 20);
		}

		$this->server = ServerPool::request($this->id, $this);
	}

	public function ready(): void
	{
		$this->ready = true;
	}

	public function stopServer(): void
	{
		if (isset($this->server)) {
			$this->server->stop();
			ServerPool::clean($this->server->getId());
		}
	}

	public function teleport(): void
	{
		if (!isset($this->server) || !$this->ready) {
			Server::getInstance()->getLogger()->critical("Container not available! Shutting down...");
			ServerPool::clean($this->id);
			return;
		}
		QueueManager::unbind($this);
		$this->ticker->cancel();
		$packet = new InitializePacket();
		if ($this->gamemode instanceof TeamGamemode) {
			$partyData = [];
			foreach ($this->players as $player) {
				$party = PartyManager::get($player);
				$partyData[$player->getName()] = $party->getOwner();
			}
			$parties = [];
			foreach (array_unique($partyData) as $party) {
				$members = array_keys($partyData, $party, true);
				shuffle($members);
				$parties[$party] = $members;
			}
			usort($parties, static function ($a, $b): int {
				return count($b) - count($a);
			});
			$teams = array_fill(0, $this->gamemode->getTeamCount(), []);
			$space = array_fill(0, $this->gamemode->getTeamCount(), $this->gamemode->getTeamSize());
			$current = 0;
			while (isset($parties[0][0])) {
				if ($space[$current] > 0) {
					$teams[$current][] = $parties[0][0];
					$space[$current]--;
					unset($parties[0][0]);
					if ($parties[0] === []) {
						unset($parties[0]);
						$parties = array_values($parties);
						$current = array_keys($space, max($space), true)[0];
					} else {
						$parties[0] = array_values($parties[0]);
					}
				} else {
					$current = array_keys($space, max($space), true)[0];
					usort($parties, static function ($a, $b): int {
						return count($b) - count($a);
					});
				}
			}
			if (count($teams[1]) === 0) {
				$chunks = array_chunk($teams[0], (int) ceil(count($teams[0]) / 2));
				$teams[0] = $chunks[0];
				$teams[1] = $chunks[1];
			}
			$packet->teams = $teams;
			$packet->teamCount = $this->gamemode->getTeamCount();
			$packet->teamSize = $this->gamemode->getTeamCount();
		}
		$packet->players = $this->players;
		$packet->gamemodeId = $this->gamemode->getId();
		$packet->minigame = $this->gamemode->getMinigame()->getId();
		StarGateUtil::sendTo($this->server->getServerName(), $packet);
		foreach ($this->players as $player) {
			StarGateAtlantis::getInstance()->transferPlayer($player, $this->server->getServerName());
		}
		ServerPool::clean($this->server->getId());
	}
}