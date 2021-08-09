<?php

namespace LobbySystem\packets\party\info;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\utils\Convertor;
use LobbySystem\utils\PlayerCache;

class PartyQuitPacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var bool
	 */
	public $kick;
	/**
	 * @var array
	 */
	public $party;

	public function __construct()
	{
		parent::__construct("PARTY_INFO_QUIT", 0x127);
	}

	public function decode(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
		$this->kick = (bool) $data[2];
		$this->party = explode("#", $data[3]);
	}

	public function encode(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(PlayerCache::get($this->player));
		$convertor->putString(PlayerCache::get($this->kick));
		$convertor->putString(implode("#", PlayerCache::getRecursive($this->party)));

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}
}