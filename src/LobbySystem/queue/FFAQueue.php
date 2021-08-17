<?php

namespace LobbySystem\queue;

use alemiz\sga\StarGateAtlantis;
use LobbySystem\gamemode\Gamemode;
use LobbySystem\Loader;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use UnexpectedValueException;

class FFAQueue extends Queue
{
	/**
	 * @var Gamemode
	 */
	private $gamemode;

	/**
	 * @param Player $player
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add(Player $player): void
	{
		if (!isset($this->server)) {
			$this->startServer();
		} elseif (!$this->server->isRunning()) {
			Server::getInstance()->getAsyncPool()->submitTask(new StartFFAServerTask($this->gamemode->getId()));
		}
		$name = $player->getName();
		Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($name): void {
			StarGateAtlantis::getInstance()->transferPlayer($name, $this->gamemode->getId());
		}), 20 * 10);
	}

	/**
	 * @param Player $player
	 */
	public function remove(Player $player): void
	{
		throw new UnexpectedValueException($this->gamemode->getId() . " is not startable");
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function contains(Player $player): bool
	{
		return false;
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
	public function getSize(): int
	{
		return 0;
	}

	public function tick(): void
	{
		throw new UnexpectedValueException($this->gamemode->getId() . " is not startable");
	}

	public function startServer(): void
	{
		throw new UnexpectedValueException($this->gamemode->getId() . " is not startable");
	}

	public function stopServer(): void
	{
		throw new UnexpectedValueException($this->gamemode->getId() . " is not startable");
	}

	public function teleport(): void
	{
		throw new UnexpectedValueException($this->gamemode->getId() . " is not startable");
	}
}