<?php

namespace LobbySystem\packets\party\request;

use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\StarGateAtlantis;
use alemiz\sga\utils\Convertor;
use LobbySystem\packets\PacketPool;

class InviteRequestPacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var string
	 */
	public $inviter;
	/**
	 * @var string
	 */
	public $from;

	public function decodePayload(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
		$this->inviter = $data[2];
		$this->from = $data[3];
	}

	public function encodePayload(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(strtolower($this->player));
		$convertor->putString(strtolower($this->inviter));
		$convertor->putString(StarGateAtlantis::getInstance()->getClientName());

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}

	public function getPacketId(): int
	{
		return PacketPool::PARTY_REQUEST_INVITE;
	}
}