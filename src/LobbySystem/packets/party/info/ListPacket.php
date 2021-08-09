<?php

namespace LobbySystem\packets\party\info;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\PlayerCache;

class ListPacket extends NetworkPacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var string
	 */
	public $owner;
	/**
	 * @var string[]
	 */
	public $moderators;
	/**
	 * @var string[]
	 */
	public $members;
	/**
	 * @var array<string, bool>
	 */
	public $online;

	public function decodePayload(): void
	{
		$this->player = $this->getString();
		$this->owner = $this->getString();
		$this->moderators = $this->getStringArray();
		$this->members = $this->getStringArray();
		$this->online = $this->getKeyedBoolArray();
	}

	public function encodePayload(): void
	{
		$this->putString(PlayerCache::get($this->player));
		$this->putString(PlayerCache::get($this->owner));
		$this->putStringArray(PlayerCache::getRecursive($this->moderators));
		$this->putStringArray(PlayerCache::getRecursive($this->members));
		$this->putKeyedBoolArray(PlayerCache::getRecursiveKeys($this->online));
	}

	public function getPacketId(): int
	{
		return PacketPool::PARTY_INFO_LIST;
	}
}