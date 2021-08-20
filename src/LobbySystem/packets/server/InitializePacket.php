<?php

namespace LobbySystem\packets\server;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;

class InitializePacket extends NetworkPacket
{
	/**
	 * @var string
	 */
	public $gamemodeId;
	/**
	 * @var string
	 */
	public $minigame;
	/**
	 * @var int
	 */
	public $teamCount = 0;
	/**
	 * @var int
	 */
	public $teamSize = 0;
	/**
	 * @var string[]
	 */
	public $players = [];
	/**
	 * @var string[][]
	 */
	public $teams = [];

	public function decodePayload(): void
	{
		$this->gamemodeId = $this->getString();
		$this->minigame = $this->getString();
		$this->teamCount = $this->getInt();
		$this->teamSize = $this->getInt();
		$this->players = $this->getStringArray();
		$this->teams = $this->getArray(function (): array {
			return $this->getStringArray();
		});
	}

	public function encodePayload(): void
	{
		$this->putString($this->gamemodeId);
		$this->putString($this->minigame);
		$this->putString($this->teamCount);
		$this->putString($this->teamSize);
		$this->putStringArray($this->players);
		$this->putArray($this->teams, function (array $team): void {
			$this->putStringArray($team);
		});
	}

	public function getPacketId(): int
	{
		return PacketPool::SERVER_INITIALIZE;
	}
}