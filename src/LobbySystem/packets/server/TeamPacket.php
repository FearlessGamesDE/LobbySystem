<?php

namespace LobbySystem\packets\server;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;

class TeamPacket extends NetworkPacket
{
	/**
	 * @var string[]
	 */
	public $team;

	public function decodePayload(): void
	{
		$this->team = $this->getStringArray();
	}

	public function encodePayload(): void
	{
		$this->putStringArray($this->team);
	}

	public function getPacketId(): int
	{
		return PacketPool::SERVER_TEAM;
	}
}