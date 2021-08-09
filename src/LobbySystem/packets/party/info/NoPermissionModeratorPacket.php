<?php

namespace LobbySystem\packets\party\info;

use alemiz\sga\packets\StarGatePacket;
use alemiz\sga\utils\Convertor;
use LobbySystem\utils\PlayerCache;

class NoPermissionModeratorPacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;

	public function __construct()
	{
		parent::__construct("PARTY_INFO_NOPERMISSION_MODERATOR", 0x12A);
	}

	public function decode(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
	}

	public function encode(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(PlayerCache::get($this->player));

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}
}