<?php

namespace LobbySystem\queue;

use LobbySystem\Loader;
use LobbySystem\utils\StarGateUtil;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use UnexpectedValueException;

class FFAQueue extends Queue
{
	/**
	 * @param Player $player
	 */
	public function add(Player $player): void
	{
		if (!isset($this->server)) {
			$this->startServer();
		} elseif (!$this->server->isRunning()) {
			Server::getInstance()->getAsyncPool()->submitTask(new StartFFAServerTask($this->getGamemode()->getId()));
		}
		$name = $player->getName();
		Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($name): void {
			StarGateUtil::transferPlayer($name, $this->getGamemode()->getId());
		}), 20 * 10);
	}

	/**
	 * @param Player $player
	 */
	public function remove(Player $player): void
	{
		throw new UnexpectedValueException($this->getGamemode()->getId() . " is not startable");
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
	 * @return int
	 */
	public function getSize(): int
	{
		return 0;
	}

	public function tick(): void
	{
		throw new UnexpectedValueException($this->getGamemode()->getId() . " is not startable");
	}

	public function startServer(): void
	{
		throw new UnexpectedValueException($this->getGamemode()->getId() . " is not startable");
	}

	public function stopServer(): void
	{
		throw new UnexpectedValueException($this->getGamemode()->getId() . " is not startable");
	}

	public function teleport(): void
	{
		throw new UnexpectedValueException($this->getGamemode()->getId() . " is not startable");
	}
}