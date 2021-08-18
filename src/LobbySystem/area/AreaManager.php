<?php

namespace LobbySystem\area;

use InvalidArgumentException;
use LobbySystem\Loader;
use LobbySystem\utils\WorldLoader;
use LobbySystem\utils\LobbyWorld;
use pocketmine\block\tile\Sign;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\AxisAlignedBB;
use pocketmine\world\Position;
use UnexpectedValueException;
use pocketmine\Server;

class AreaManager
{
	/**
	 * @var Area[]
	 */
	private static $areas = [];
	/**
	 * @var array<string, array{Position, AxisAlignedBB}>
	 */
	private static $places = [];

	public static function load(): void
	{
		Server::getInstance()->getPluginManager()->registerEvents(new AreaListener(), Loader::getInstance());
		foreach (WorldLoader::getTiles(LobbyWorld::get()) as $tile) {
			if ($tile instanceof Sign && $tile->getText()->getLine(0) === "AREA") {
				[$x1, $y1, $z1] = explode(";", str_replace("inf", (string) PHP_INT_MAX, str_replace("-inf", (string) PHP_INT_MIN, $tile->getText()->getLine(2))));
				[$x2, $y2, $z2] = explode(";", str_replace("inf", (string) PHP_INT_MAX, str_replace("-inf", (string) PHP_INT_MIN, $tile->getText()->getLine(3))));
				if ($x1 < $x2) {
					$minX = (int) $x1;
					$maxX = (int) $x2;
				} else {
					$minX = (int) $x2;
					$maxX = (int) $x1;
				}
				if ($y1 < $y2) {
					$minY = (int) $y1;
					$maxY = (int) $y2;
				} else {
					$minY = (int) $y2;
					$maxY = (int) $y1;
				}
				if ($z1 < $z2) {
					$minZ = (int) $z1;
					$maxZ = (int) $z2;
				} else {
					$minZ = (int) $z2;
					$maxZ = (int) $z1;
				}
				self::$places[$tile->getText()->getLine(1)] = [$tile->getPos(), new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ)];
				LobbyWorld::get()->setBlock($tile->getPos(), VanillaBlocks::AIR());
			}
		}
	}

	/**
	 * @param Area $area
	 * @return array{Position, AxisAlignedBB}
	 */
	public static function register(Area $area): array
	{
		if (isset(self::$places[$area->getName()])) {
			if (self::checkOverlap(self::$places[$area->getName()][1])) {
				throw new InvalidArgumentException("Area {$area->getName()} is overlapping with another Area");
			}
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
		foreach (self::getAreas() as $area) {
			if ($area->getBoundingBox()->intersectsWith($boundingBox)) {
				return true;
			}
		}
		return false;
	}
}