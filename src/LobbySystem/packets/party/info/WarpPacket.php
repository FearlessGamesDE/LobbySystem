<?php

namespace LobbySystem\packets\party\info;

use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\utils\Convertor;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\PlayerCache;

class WarpPacket extends StarGatePacket
{
	/**
	 * @var array
	 */
	public $party;

	public function decodePayload(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->party = explode("#", $data[1]);
	}

	public function encodePayload(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(implode("#", PlayerCache::getRecursive($this->party)));

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}

	public function getPacketId(): int
	{
		return PacketPool::PARTY_INFO_WARP;
	}
}