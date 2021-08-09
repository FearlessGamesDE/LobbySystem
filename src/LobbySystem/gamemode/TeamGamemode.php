<?php

namespace LobbySystem\gamemode;

use LobbySystem\gamemode\minigame\Minigame;
use LobbySystem\utils\Generator;

class TeamGamemode extends Gamemode
{
	/**
	 * @var int
	 */
	private $teamCount;
	/**
	 * @var int
	 */
	private $teamSize;

	/**
	 * TeamGamemode constructor.
	 * @param Minigame $minigame
	 * @param string $name
	 * @param int $teamCount
	 * @param int $teamSize
	 */
	public function __construct(Minigame $minigame, string $name, int $teamCount, int $teamSize)
	{
		$this->teamCount = $teamCount;
		$this->teamSize = $teamSize;
		parent::__construct($minigame, $name, $teamCount * $teamSize);
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return implode("_", [$this->getMinigame()->getId(), Generator::toString($this->teamCount), Generator::toString($this->teamSize)]);
	}

	/**
	 * @return int
	 */
	public function getTeamCount(): int
	{
		return $this->teamCount;
	}

	/**
	 * @return int
	 */
	public function getTeamSize(): int
	{
		return $this->teamSize;
	}
}