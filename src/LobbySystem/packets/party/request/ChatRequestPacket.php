<?php

namespace LobbySystem\packets\party\request;

use LobbySystem\packets\NetworkPacket;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\StarGateUtil;

class ChatRequestPacket extends NetworkPacket
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

	public function decodePayload(): void
	{
		$this->player = $this->getString();
		$this->message = $this->getString();
		$this->from = $this->getString();
	}

	public function encodePayload(): void
	{
		$this->putString(strtolower($this->player));
		$this->putString($this->message);
		$this->putString(StarGateUtil::getClient()->getClientName());
	}

	public function getPacketId(): int
	{
		return PacketPool::PARTY_REQUEST_CHAT;
	}
}