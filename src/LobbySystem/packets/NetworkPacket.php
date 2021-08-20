<?php

namespace LobbySystem\packets;

use alemiz\sga\codec\StarGatePacketHandler;
use alemiz\sga\protocol\StarGatePacket;
use Closure;

abstract class NetworkPacket extends StarGatePacket
{
	/**
	 * @param StarGatePacketHandler $handler
	 * @return bool
	 */
	public function handle(StarGatePacketHandler $handler): bool
	{
		return PacketHandler::handle($this);
	}

	/**
	 * @param string $string
	 */
	public function putString(string $string): void
	{
		$this->putInt(strlen($string));
		$this->put($string);
	}

	/**
	 * @return string
	 */
	public function getString(): string
	{
		return $this->get($this->getInt());
	}

	/**
	 * @param array<int, mixed>     $array
	 * @param Closure(mixed) : void $serializer
	 */
	public function putArray(array $array, Closure $serializer): void
	{
		$this->putInt(count($array));
		foreach ($array as $element) {
			$serializer($element);
		}
	}

	/**
	 * @param Closure() : mixed $serializer
	 * @return string[]
	 */
	public function getArray(Closure $serializer): array
	{
		$count = $this->getInt();
		$array = [];
		for ($i = 0; $i < $count; $i++) {
			$array[] = $serializer();
		}
		return $array;
	}

	/**
	 * @param string[] $array
	 * @return void
	 */
	public function putStringArray(array $array): void
	{
		$this->putInt(count($array));
		foreach ($array as $string) {
			$this->putString($string);
		}
	}

	/**
	 * @return string[]
	 */
	public function getStringArray(): array
	{
		$count = $this->getInt();
		$array = [];
		for ($i = 0; $i < $count; $i++) {
			$array[] = $this->getString();
		}
		return $array;
	}

	/**
	 * @param bool[] $array
	 * @return void
	 */
	public function putBoolArray(array $array): void
	{
		$this->putInt(count($array));
		foreach ($array as $bool) {
			$this->putBool($bool);
		}
	}

	/**
	 * @return bool[]
	 */
	public function getBoolArray(): array
	{
		$count = $this->getInt();
		$array = [];
		for ($i = 0; $i < $count; $i++) {
			$array[] = $this->getBool();
		}
		return $array;
	}

	/**
	 * @param bool[] $array
	 * @return void
	 */
	public function putKeyedBoolArray(array $array): void
	{
		$this->putInt(count($array));
		foreach ($array as $string => $bool) {
			$this->putString($string);
			$this->putBool($bool);
		}
	}

	/**
	 * @return bool[]
	 */
	public function getKeyedBoolArray(): array
	{
		$count = $this->getInt();
		$array = [];
		for ($i = 0; $i < $count; $i++) {
			$array[$this->getString()] = $this->getBool();
		}
		return $array;
	}
}