<?php

namespace LobbySystem\packets\party\info;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\utils\Convertor;
use LobbySystem\utils\PlayerCache;

class PromotePacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var bool
	 */
	public $moderator;
	/**
	 * @var array
	 */
	public $party;

	public function __construct()
	{
		parent::__construct("PARTY_INFO_PROMOTE", 0x130);
	}

	public function decode(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
		$this->moderator = ($data[2] === "moderator");
		$this->party = explode("#", $data[3]);
	}

	public function encode(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(PlayerCache::get($this->player));
		$convertor->putString(($this->moderator ? "moderator" : "owner"));
		$convertor->putString(implode("#", PlayerCache::getRecursive($this->party)));

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}
}