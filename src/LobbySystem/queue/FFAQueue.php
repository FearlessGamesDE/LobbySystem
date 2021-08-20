<?php

namespace LobbySystem\queue;

use LobbySystem\server\ServerPool;
use LobbySystem\utils\StarGateUtil;
use pocketmine\player\Player;
use UnexpectedValueException;

class FFAQueue extends Queue
{
	/**
	 * @param Player $player
	 */
	public function add(Player $player): void
	{
		if (!isset($this->server)) {
			$this->server = ServerPool::request($this->getGamemode()->getId(), $this);
		}
		if ($this->server->ready()) {
			StarGateUtil::transferPlayer($player->getName(), $this->getGamemode()->getId());
		} else {
			$this->players[$player->getName()] = $player;
		}
	}

	/**
	 * @param Player $player
	 */
	public function remove(Player $player): void
	{
		unset($this->players[$player->getName()]);
	}

	/**
	 * @return int
	 */
	public function getSize(): int
	{
		return 0;
	}

	public function ready(): void
	{
		foreach ($this->players as $player) {
			StarGateUtil::transferPlayer($player->getName(), $this->getGamemode()->getId());
		}
		$this->players = [];
	}

	public function tick(): void
	{
		//used by start command while waiting
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