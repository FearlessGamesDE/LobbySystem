<?php

namespace LobbySystem\packets\server;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\StarGateUtil;

class RejoinInformationPacket extends NetworkPacket
{
	/**
	 * @var string
	 */
	public $serverName;
	/**
	 * @var string
	 */
	public $player;

	public function decodePayload(): void
	{
		$this->serverName = $this->getString();
		$this->player = $this->getString();
	}

	public function encodePayload(): void
	{
		$this->putString(StarGateUtil::getClient()->getClientName());
		$this->putString($this->player);
	}

	public function getPacketId(): int
	{
		return PacketPool::SERVER_REJOIN_INFORMATION;
	}
}