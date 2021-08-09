<?php

namespace LobbySystem\packets\server;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;

class PlayPacket extends NetworkPacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var string
	 */
	public $gamemode;

	public function decodePayload(): void
	{
		$this->player = $this->getString();
		$this->gamemode = $this->getString();
	}

	public function encodePayload(): void
	{
		$this->putString(strtolower($this->player));
		$this->putString(strtolower($this->gamemode));
	}

	public function getPacketId(): int
	{
		return PacketPool::SERVER_PLAY;
	}
}