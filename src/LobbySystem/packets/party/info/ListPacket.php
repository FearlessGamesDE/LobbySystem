<?php

namespace LobbySystem\packets\party\info;

use alemiz\sga\protocol\StarGatePacket;
use alemiz\sga\utils\Convertor;
use LobbySystem\packets\PacketPool;
use LobbySystem\utils\PlayerCache;

class ListPacket extends StarGatePacket
{
	/**
	 * @var string
	 */
	public $player;
	/**
	 * @var string
	 */
	public $owner;
	/**
	 * @var string[]
	 */
	public $moderators;
	/**
	 * @var string[]
	 */
	public $members;
	/**
	 * @var bool[]
	 */
	public $online;

	public function decodePayload(): void
	{
		$this->isEncoded = false;

		$data = Convertor::getPacketStringData($this->encoded);

		$this->player = $data[1];
		$this->owner = $data[2];
		$this->moderators = array_filter(explode("#", $data[3]));
		$this->members = array_filter(explode("#", $data[4]));
		$this->online = array_combine(explode("#", $data[6]), explode("#", $data[5]));
		foreach ($this->online as $i => $o){
			$this->online[$i] = (bool) $o;
		}
	}

	public function encodePayload(): void
	{
		$convertor = new Convertor($this->getID());

		$convertor->putString(PlayerCache::get($this->player));
		$convertor->putString(PlayerCache::get($this->owner));
		$convertor->putString(implode("#", PlayerCache::getRecursive($this->moderators)));
		$convertor->putString(implode("#", PlayerCache::getRecursive($this->members)));
		$convertor->putString(implode("#", $this->online));
		$convertor->putString(implode("#", PlayerCache::getRecursive(array_keys($this->online))));

		$this->encoded = $convertor->getPacketString();
		$this->isEncoded = true;
	}

	public function getPacketId(): int
	{
		return PacketPool::PARTY_INFO_LIST;
	}
}