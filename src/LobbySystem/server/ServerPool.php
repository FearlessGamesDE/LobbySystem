<?php

namespace LobbySystem\server;

use LobbySystem\queue\Queue;

class ServerPool
{
	/**
	 * @var ServerPoolEntry[]
	 */
	private static $entries = [];
	/**
	 * @var ServerPoolEntry[]
	 */
	private static $addresses = [];

	/**
	 * @param string $id
	 * @param Queue  $queue
	 * @return ServerPoolEntry
	 */
	public static function request(string $id, Queue $queue): ServerPoolEntry
	{
		if (isset(self::$entries[$id])) {
			return self::$entries[$id];
		}
		self::$entries[$id] = $entry = new ServerPoolEntry($id, $queue);
		$entry->start();
		return $entry;
	}

	/**
	 * @param string $id
	 * @return ServerPoolEntry
	 */
	public static function get(string $id): ServerPoolEntry
	{
		return self::$entries[$id];
	}

	/**
	 * @param string $id
	 */
	public static function clean(string $id): void
	{
		unset(self::$addresses[self::$entries[$id]->getServerName()], self::$entries[$id]);
	}

	/**
	 * @param string $id
	 * @return ServerPoolEntry
	 */
	public static function setAddress(string $id): ServerPoolEntry
	{
		return self::$addresses[self::$entries[$id]->getServerName()] = self::$entries[$id];
	}

	/**
	 * @param string $address
	 * @return ServerPoolEntry
	 */
	public static function getAddress(string $address): ServerPoolEntry
	{
		return self::$addresses[$address];
	}
}