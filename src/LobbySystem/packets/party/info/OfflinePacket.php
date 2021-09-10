<?php

namespace LobbySystem\packets\party\info;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\PlayerCache;

class OfflinePacket extends NetworkPacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var string
	 */
	public $requester;

	public function decodePayload(): void
	{
		$this->player = $this->getString();
		$this->requester = $this->getString();
	}

	public function encodePayload(): void
	{
		$this->putString(PlayerCache::get($this->player));
		$this->putString(PlayerCache::get($this->requester));
	}

	public function getPacketId(): int
	{
		return PacketPool::PARTY_INFO_OFFLINE;
	}
}