<?php

namespace LobbySystem\packets\server;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\utils\Convertor;

class EnablePacket extends StarGatePacket
{
	public function __construct()
	{
		parent::__construct("SERVER_ENABLE", 0x110);
	}

	public function decode(): void
	{
		$this->isEncoded = false;
	}

	public function encode(): void
	{
		$convertor = new Convertor($this->getID());
		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}
}