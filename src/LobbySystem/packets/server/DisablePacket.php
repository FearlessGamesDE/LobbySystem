<?php

namespace LobbySystem\packets\server;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\utils\Convertor;

class DisablePacket extends StarGatePacket
{
	public function __construct()
	{
		parent::__construct("SERVER_DISABLE", 0x111);
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