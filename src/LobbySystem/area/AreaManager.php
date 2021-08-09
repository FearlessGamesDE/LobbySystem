<?php

namespace LobbySystem\area;

use LobbySystem\Loader;
use LobbySystem\utils\LevelLoader;
use LobbySystem\utils\LobbyLevel;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use \UnexpectedValueException;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\Server;
use pocketmine\tile\Sign;

class AreaManager
{
	/**
	 * @var Area[]
	 */
	private static $areas = [];
	/**
	 * @var array
	 */
	private static $places = [];

	public static function load()
	{
		Server::getInstance()->getPluginManager()->registerEvents(new AreaListener(), Loader::getInstance());
		LevelLoader::loadLevel(LobbyLevel::get());
		foreach (LobbyLevel::get()->getTiles() as $tile) {
			if ($tile instanceof Sign) {
				if ($tile->getLine(0) === "AREA") {
					[$x1, $y1, $z1] = explode(";", str_replace("inf", PHP_INT_MAX, str_replace("-inf", PHP_INT_MIN, $tile->getLine(2))));
					[$x2, $y2, $z2] = explode(";", str_replace("inf", PHP_INT_MAX, str_replace("-inf", PHP_INT_MIN, $tile->getLine(3))));
					if($x1 < $x2){
						$minX = $x1;
						$maxX = $x2;
					}else{
						$minX = $x2;
						$maxX = $x1;
					}
					if($y1 < $y2){
						$minY = $y1;
						$maxY = $y2;
					}else{
						$minY = $y2;
						$maxY = $y1;
					}
					if($z1 < $z2){
						$minZ = $z1;
						$maxZ = $z2;
					}else{
						$minZ = $z2;
						$maxZ = $z1;
					}
					self::$places[$tile->getLine(1)] = [$tile->asPosition(), new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ)];
					$tile->getLevelNonNull()->setBlock($tile, BlockFactory::get(BlockIds::AIR));
				}
			}
		}
	}

	/**
	 * @param Area $area
	 * @return array
	 */
	public static function register(Area $area): array
	{
		if (isset(self::$places[$area->getName()])) {
			self::$areas[$area->getName()] = $area;
			return self::$places[$area->getName()];
		}

		throw new UnexpectedValueException("Unknown Area name " . $area->getName());
	}

	/**
	 * @return Area[]
	 */
	public static function getAreas(): array
	{
		return self::$areas;
	}

	/**
	 * @param AxisAlignedBB $boundingBox
	 * @return bool
	 */
	public static function checkOverlap(AxisAlignedBB $boundingBox): bool
	{
		foreach (self::getAreas() as $area){
			if($area->getBoundingBox()->intersectsWith($boundingBox)){
				return true;
			}
		}
		return false;
	}
}