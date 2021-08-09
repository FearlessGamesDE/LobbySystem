<?php

namespace LobbySystem\packets\party\request;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\StarGateAtlantis;
use alemiz\sga\utils\Convertor;

class KickRequestPacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var string
	 */
	public $kicker;
	/**
	 * @var string
	 */
	public $from;

	public function __construct()
	{
		parent::__construct("PARTY_REQUEST_KICK", 0x131);
	}

	public function decode(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
		$this->kicker = $data[2];
		$this->from = $data[3];
	}

	public function encode(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(strtolower($this->player));
		$convertor->putString(strtolower($this->kicker));
		$convertor->putString(StarGateAtlantis::getInstance()->getClientName());

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}
}