<?php

namespace LobbySystem\utils;

use LobbySystem\Loader;
use pocketmine\block\tile\Tile;
use pocketmine\world\format\io\leveldb\LevelDB;
use pocketmine\world\World;

class WorldLoader
{
	/**
	 * @param World $world
	 * @return \Generator<Tile>
	 */
	public static function getTiles(World $world): \Generator
	{
		$provider = $world->getProvider();
		if ($provider instanceof LevelDB) {
			foreach ($provider->getAllChunks(true, Loader::getInstance()->getLogger()) as $chunk) {
				foreach ($chunk->getTiles() as $tile) {
					yield $tile;
				}
			}
		}
	}
}