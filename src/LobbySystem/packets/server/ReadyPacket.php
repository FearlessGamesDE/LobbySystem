<?php

namespace LobbySystem\packets\server;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\StarGateUtil;

class ReadyPacket extends NetworkPacket
{
	/**
	 * @var string
	 */
	public $serverName;

	public function decodePayload(): void
	{
		$this->serverName = $this->getString();
	}

	public function encodePayload(): void
	{
		$this->putString(StarGateUtil::getClient()->getClientName());
	}

	public function getPacketId(): int
	{
		return PacketPool::SERVER_READY;
	}
}