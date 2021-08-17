<?php

namespace LobbySystem\queue;

use alemiz\sga\StarGateAtlantis;
use LobbySystem\gamemode\Gamemode;
use pocketmine\player\Player;
use UnexpectedValueException;

class FreeQueue extends Queue
{
	/**
	 * @var Gamemode
	 */
	private $gamemode;

	/**
	 * @param Player $player
	 */
	public function add(Player $player): void
	{
		StarGateAtlantis::getInstance()->transferPlayer($player, $this->gamemode->getId());
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