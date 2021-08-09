<?php

namespace LobbySystem\packets\server;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\utils\Convertor;

class TeamPacket extends StarGatePacket
{
	/**
	 * @var array
	 */
	public $team;

	public function __construct()
	{
		parent::__construct("SERVER_TEAM", 0x115);
	}

	public function decode(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->team = array_filter(explode("#", $data[1]));
	}

	public function encode(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(implode("#", $this->team));

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}
}