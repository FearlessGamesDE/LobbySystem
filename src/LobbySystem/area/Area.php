<?php

namespace LobbySystem\area;

use LobbySystem\utils\LobbyWorld;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\math\AxisAlignedBB;
use pocketmine\player\Player;
use pocketmine\world\Position;

abstract class Area
{
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var Position
	 */
	private $spawn;
	/**
	 * @var AxisAlignedBB
	 */
	private $boundingBox;
	/**
	 * @var Player[]
	 */
	private $players;

	/**
	 * Area constructor.
	 * @param string $name
	 */
	public function __construct(string $name)
	{
		$this->name = $name;
		$data = AreaManager::register($this);
		$this->spawn = $data[0];
		$this->boundingBox = $data[1];
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return Position
	 */
	public function getSpawn(): Position
	{
		return $this->spawn;
	}

	/**
	 * @return AxisAlignedBB
	 */
	public function getBoundingBox(): AxisAlignedBB
	{
		return $this->boundingBox;
	}

	/**
	 * @param Player $player
	 * @return bool whether the Player is allowed to enter
	 */
	abstract public function onEnter(Player $player): bool;

	/**
	 * @param Player $player
	 * @return bool whether the Player is allowed to leave
	 */
	abstract public function onLeave(Player $player): bool;

	/**
	 * @param Player $player
	 * @return bool whether the Player should spawn at the area spawn
	 */
	abstract public function onDeath(Player $player): bool;

	/**
	 * @param Player $player
	 * @param Block  $block
	 * @return bool whether the Player is allowed to place this Block
	 */
	abstract public function onPlace(Player $player, Block $block): bool;

	/**
	 * @param Player $player
	 * @param Block  $block
	 * @return bool whether the Player is allowed to break this Block
	 */
	abstract public function onBreak(Player $player, Block $block): bool;

	/**
	 * @param int         $type
	 * @param Player      $player
	 * @param Entity|null $damager
	 * @return bool whether the Player is allowed to get damaged
	 */
	abstract public function onDamage(int $type, Player $player, ?Entity $damager): bool;

	/**
	 * @param Player $player
	 * @return bool whether the Player is allowed to get exhausted
	 */
	abstract public function onExhaust(Player $player): bool;

	/**
	 * @return Player[]
	 */
	public function getPlayers(): array
	{
		return $this->players;
	}

	/**
	 * @param Player $player
	 */
	public function addPlayer(Player $player): void
	{
		$this->players[$player->getName()] = $player;
	}

	/**
	 * @param Player $player
	 */
	public function removePlayer(Player $player): void
	{
		unset($this->players[$player->getName()]);
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function containsPlayer(Player $player): bool
	{
		return isset($this->players[$player->getName()]);
	}

	/**
	 * @param Player $player
	 */
	public function kickPlayer(Player $player): void
	{
		if ($this->containsPlayer($player)) {
			$player->teleport(LobbyWorld::get()->getSafeSpawn());
		}
	}
}