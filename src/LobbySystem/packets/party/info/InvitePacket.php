<?php

namespace LobbySystem\packets\party\info;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\utils\Convertor;
use LobbySystem\utils\PlayerCache;

class InvitePacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var array
	 */
	public $party;

	public function __construct()
	{
		parent::__construct("PARTY_INFO_INVITE", 0x122);
	}

	public function decode(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
		$this->party = explode("#", $data[2]);
	}

	public function encode(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(PlayerCache::get($this->player));
		$convertor->putString(implode("#", PlayerCache::getRecursive($this->party)));

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}
}