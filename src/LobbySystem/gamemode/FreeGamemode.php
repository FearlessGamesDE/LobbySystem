<?php

namespace LobbySystem\gamemode;

use LobbySystem\gamemode\minigame\Minigame;
use LobbySystem\queue\FreeQueue;
use LobbySystem\queue\Queue;

class FreeGamemode extends Gamemode
{
	/**
	 * @var FreeQueue
	 */
	private $freeQueue;

	/**
	 *FreeGamemode constructor.
	 * @param Minigame $minigame
	 * @param string $name
	 */
	public function __construct(Minigame $minigame, string $name)
	{
		parent::__construct($minigame, $name, PHP_INT_MAX, 0);
		$this->freeQueue = new FreeQueue($this);
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->getMinigame()->getId();
	}

	/**
	 * @param int $space
	 * @return FreeQueue
	 */
	public function getQueue(int $space = 1): Queue
	{
		return $this->freeQueue;
	}
}