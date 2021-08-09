<?php

namespace LobbySystem\packets\party\info;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\PlayerCache;

class InPartyPacket extends NetworkPacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var string
	 */
	public $inviter;

	public function decodePayload(): void
	{
		$this->player = $this->getString();
		$this->inviter = $this->getString();
	}

	public function encodePayload(): void
	{
		$this->putString(PlayerCache::get($this->player));
		$this->putString(PlayerCache::get($this->inviter));
	}

	public function getPacketId(): int
	{
		return PacketPool::PARTY_INFO_IN_PARTY;
	}
}