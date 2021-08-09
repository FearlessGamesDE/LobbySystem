<?php

namespace LobbySystem\packets\server;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\utils\Convertor;

class PlayerPacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;

	public function __construct()
	{
		parent::__construct("SERVER_PLAYER", 0x112);
	}

	public function decode(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
	}

	public function encode(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString($this->player);

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}
}