<?php

namespace LobbySystem\packets\server;

use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\utils\Convertor;
use LobbySystem\packets\PacketPool;

class TeamPacket extends StarGatePacket
{
	/**
	 * @var array
	 */
	public $team;

	public function decodePayload(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->team = array_filter(explode("#", $data[1]));
	}

	public function encodePayload(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(implode("#", $this->team));

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}

	public function getPacketId(): int
	{
		return PacketPool::SERVER_TEAM;
	}
}