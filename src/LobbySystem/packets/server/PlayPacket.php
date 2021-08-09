<?php

namespace LobbySystem\packets\server;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\utils\Convertor;

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

	public function __construct()
	{
		parent::__construct("SERVER_PLAY", 0x114);
	}

	public function decode(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
		$this->gamemode = $data[2];
	}

	public function encode(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(strtolower($this->player));
		$convertor->putString(strtolower($this->gamemode));

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}
}