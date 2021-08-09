<?php /** @noinspection PhpUnusedParameterInspection */

namespace LobbySystem\queue;

use alemiz\sga\StarGateAtlantis;
use LobbySystem\Gamemode\Gamemode;
use LobbySystem\gamemode\TeamGamemode;
use LobbySystem\Loader;
use LobbySystem\packets\server\TeamPacket;
use LobbySystem\party\PartyManager;
use LobbySystem\utils\Generator;
use LobbySystem\utils\Output;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use ServerHandler\Docker\DockerContainerInstance;

class Queue
{
	/**
	 * @string
	 */
	private $id;
	/**
	 * @var Gamemode
	 */
	private $gamemode;
	/**
	 * @var Player[]
	 */
	private $players = [];
	/**
	 * @var int
	 */
	public $tick = 60;
	/**
	 * @var TaskHandler
	 */
	private $ticker;
	/**
	 * @var DockerContainerInstance
	 */
	protected $server;

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
		Output::send($this->players, "queue-join-other", ["{player}" => $player->getName(), "{current}" => count($this->players) + 1, "{max}" => $this->gamemode->getCapacity()]);
		$this->players[$player->getName()] = $player;
		Output::send($player, "queue-join", ["{game}" => $this->gamemode->getDisplayName(), "{current}" => count($this->players), "{max}" => $this->gamemode->getCapacity()]);
		if ($this->tick > 10 && count($this->players) === $this->gamemode->getCapacity()) {
			QueueManager::unbind($this);
			$this->startServer();
		} elseif (count($this->players) === $this->gamemode->getMinimum()) {
			$this->tick = 60;
			$this->ticker = Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currenttick): void {
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
		Output::send($this->players, "queue-leave-other", ["{player}" => $player->getName(), "{current}" => count($this->players), "{max}" => $this->gamemode->getCapacity()]);
		if (count($this->players) < $this->gamemode->getMinimum()) {
			$this->stopServer();
			if(isset($this->ticker)){
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
	public function contains(Player $player):bool
	{
		return isset($this->players[$player->getName()]);
	}

	/**
	 * @return Gamemode
	 */
	public function getGamemode():Gamemode
	{
		return $this->gamemode;
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getSize():int
	{
		return count($this->players);
	}

	public function tick(): void
	{
		/** @noinspection PhpExpressionResultUnusedInspection */
		$_ENV;
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
				Output::send($this->players, "start-in", ["{seconds}" => $this->tick]);
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
			$this->ticker = Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currenttick): void {
				$this->tick();
			}), 20);
		}

		Server::getInstance()->getAsyncPool()->submitTask(new StartServerTask($this->id, $this->gamemode->getId(), $this->gamemode->getMinigame()->getId()));
	}

	/**
	 * @param DockerContainerInstance $server
	 */
	public function setServer(DockerContainerInstance $server): void
	{
		$this->server = $server;
	}

	public function stopServer(): void
	{
		if (!empty($this->server)) {
			$this->server->stop();
		}
	}

	public function teleport(): void
	{
		if (!isset($this->server)) {
			Server::getInstance()->getLogger()->critical("Container not available! Shutting down...");
			Loader::getInstance()->setEnabled(false);
			return;
		}
		QueueManager::unbind($this);
		$this->ticker->cancel();
		if ($this->gamemode instanceof TeamGamemode) {
			$partyData = [];
			foreach ($this->players as $player) {
				$party = PartyManager::get($player);
				$partyData[$player->getName()] = $party->getOwner();
			}
			$parties = [];
			foreach (array_unique($partyData) as $party) {
				$members = array_keys($partyData, $party);
				shuffle($members);
				$parties[$party] = $members;
			}
			usort($parties, static function ($a, $b) {
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
					if (empty($parties[0])) {
						unset($parties[0]);
						$parties = array_values($parties);
						$current = array_keys($space, max($space))[0];
					} else {
						$parties[0] = array_values($parties[0]);
					}
				} else {
					$current = array_keys($space, max($space))[0];
					usort($parties, static function ($a, $b) {
						return count($b) - count($a);
					});
				}
			}
			if (count($teams[1]) === 0) {
				$chunks = array_chunk($teams[0], ceil(count($teams[0]) / 2));
				$teams[0] = $chunks[0];
				$teams[1] = $chunks[1];
			}
			foreach ($teams as $team) {
				$packet = new TeamPacket();
				$packet->team = $team;
				StarGateAtlantis::getInstance()->forwardPacket($this->server->getName(), "default", $packet);
			}
		}
		foreach ($this->players as $player) {
			StarGateAtlantis::getInstance()->transferPlayer($player, $this->server->getName());
		}
	}
}