<?php

namespace LobbySystem\packets;

use alemiz\sga\protocol\StarGatePacket;

abstract class NetworkPacket extends StarGatePacket
{
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