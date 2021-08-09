<?php

namespace LobbySystem\packets\server;

use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\utils\Convertor;
use LobbySystem\packets\PacketPool;

class DisablePacket extends StarGatePacket
{
	public function decodePayload(): void
	{
		$this->isEncoded = false;
	}

	public function encodePayload(): void
	{
		$convertor = new Convertor($this->getID());
		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}

	public function getPacketId(): int
	{
		return PacketPool::SERVER_DISABLE;
	}
}