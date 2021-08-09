<?php

namespace LobbySystem\packets\party\request;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\StarGateAtlantis;
use alemiz\sga\utils\Convertor;

class ChatRequestPacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var string
	 */
	public $message;
	/**
	 * @var string
	 */
	public $from;

	public function __construct()
	{
		parent::__construct("PARTY_REQUEST_CHAT", 0x134);
	}

	public function decode(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
		$this->message = str_replace("{:SIGN_SHOUT:}", "!", $data[2]);
		$this->from = $data[3];
	}

	public function encode(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(strtolower($this->player));
		$convertor->putString(str_replace("!", "{:SIGN_SHOUT:}", $this->message));
		$convertor->putString(StarGateAtlantis::getInstance()->getClientName());

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}
}