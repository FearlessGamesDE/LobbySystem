<?php

namespace LobbySystem\packets\party\info;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\PlayerCache;

class DisbandPacket extends NetworkPacket
{
	/**
	 * @var string[]
	 */
	public $party;

	public function decodePayload(): void
	{
		$this->party = $this->getStringArray();
	}

	public function encodePayload(): void
	{
		$this->putStringArray(PlayerCache::getRecursive($this->party));
	}

	public function getPacketId(): int
	{
		return PacketPool::PARTY_INFO_DISBAND;
	}
}