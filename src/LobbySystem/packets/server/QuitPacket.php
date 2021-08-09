<?php

namespace LobbySystem\packets\server;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;

class QuitPacket extends NetworkPacket
{
	/**
	 * @var string
	 */
	public $player;

	public function decodePayload(): void
	{
		$this->player = $this->getString();
	}

	public function encodePayload(): void
	{
		$this->putString($this->player);
	}

	public function getPacketId(): int
	{
		return PacketPool::SERVER_QUIT;
	}
}