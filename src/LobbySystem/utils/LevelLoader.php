<?php

namespace LobbySystem\utils;

use pocketmine\level\format\io\region\McRegion;
use pocketmine\level\Level;
use pocketmine\Server;

class LevelLoader
{
	public static function loadLevel(Level $level)
	{
		if ($level->getProvider() instanceof McRegion) {
			foreach (scandir(Server::getInstance()->getDataPath() . "worlds/" . $level->getFolderName() . "/region/") as $region) {
				[$baseX, $baseZ] = preg_match_all("/-?[0-9]+/", $region);
				$baseX *= 32;
				$baseZ *= 32;
				for ($x = 0; $x < 32; $x++) {
					for ($z = 0; $z < 32; $z++) {
						$level->loadChunk($baseX + $x, $baseZ + $z);
					}
				}
			}
		}
	}
}