<?php

namespace LobbySystem\packets\party\info;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\PlayerCache;

class PromotePacket extends NetworkPacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var bool
	 */
	public $moderator;
	/**
	 * @var string[]
	 */
	public $party;

	public function decodePayload(): void
	{
		$this->player = $this->getString();
		$this->moderator = $this->getBool();
		$this->party = $this->getStringArray();
	}

	public function encodePayload(): void
	{
		$this->putString(PlayerCache::get($this->player));
		$this->putBool($this->moderator);
		$this->putStringArray(PlayerCache::getRecursive($this->party));
	}

	public function getPacketId(): int
	{
		return PacketPool::PARTY_INFO_PROMOTE;
	}
}