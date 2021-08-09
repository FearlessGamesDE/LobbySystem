<?php

namespace LobbySystem\packets\party\info;

use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\utils\Convertor;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\PlayerCache;

class ChatPacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var bool
	 */
	public $message;
	/**
	 * @var array
	 */
	public $party;

	public function decodePayload(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
		$this->message = str_replace("{:SIGN_SHOUT:}", "!", $data[2]);
		$this->party = explode("#", $data[3]);
	}

	public function encodePayload(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(PlayerCache::get($this->player));
		$convertor->putString(str_replace("!", "{:SIGN_SHOUT:}", $this->message));
		$convertor->putString(implode("#", PlayerCache::getRecursive($this->party)));

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}

	public function getPacketId(): int
	{
		return PacketPool::PARTY_INFO_CHAT;
	}
}