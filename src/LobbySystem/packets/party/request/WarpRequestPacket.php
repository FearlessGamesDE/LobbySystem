<?php

namespace LobbySystem\packets\party\request;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\StarGateAtlantis;
use alemiz\sga\utils\Convertor;

class WarpRequestPacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var string
	 */
	public $from;

	public function __construct()
	{
		parent::__construct("PARTY_REQUEST_WARP", 0x132);
	}

	public function decode(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
		$this->from = $data[2];
	}

	public function encode(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(strtolower($this->player));
		$convertor->putString(StarGateAtlantis::getInstance()->getClientName());

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}
}