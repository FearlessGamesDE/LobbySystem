<?php

namespace LobbySystem\packets\server;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;

class EnablePacket extends NetworkPacket
{
	public function decodePayload(): void { }

	public function encodePayload(): void { }

	public function getPacketId(): int
	{
		return PacketPool::SERVER_ENABLE;
	}
}