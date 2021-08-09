<?php

namespace LobbySystem\packets\server;

use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\utils\Convertor;
use LobbySystem\packets\PacketPool;

class PlayPacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var string
	 */
	public $gamemode;

	public function decodePayload(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
		$this->gamemode = $data[2];
	}

	public function encodePayload(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(strtolower($this->player));
		$convertor->putString(strtolower($this->gamemode));

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}

	public function getPacketId(): int
	{
		return PacketPool::SERVER_PLAY;
	}
}