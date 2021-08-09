<?php

namespace LobbySystem\packets\party\info;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\PlayerCache;

class NoPermissionModeratorPacket extends NetworkPacket
{
	/**
	 * @var string
	 */
	public $player;

	public function decodePayload(): void
	{
		$this->player = $this->getString();
	}

	public function encodePayload(): void
	{
		$this->putString(PlayerCache::get($this->player));
	}

	public function getPacketId(): int
	{
		return PacketPool::PARTY_INFO_NOPERMISSION_MODERATOR;
	}
}