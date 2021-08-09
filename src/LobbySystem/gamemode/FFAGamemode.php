<?php

namespace LobbySystem\gamemode;

use LobbySystem\gamemode\minigame\Minigame;
use LobbySystem\queue\FFAQueue;
use LobbySystem\queue\Queue;

class FFAGamemode extends Gamemode
{
	/**
	 * @var FFAQueue
	 */
	private $ffaQueue;

	/**
	 * FFAGamemode constructor.
	 * @param Minigame $minigame
	 * @param string $name
	 */
	public function __construct(Minigame $minigame, string $name)
	{
		parent::__construct($minigame, $name, PHP_INT_MAX, 0);
		$this->ffaQueue = new FFAQueue($this);
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
	 * @return FFAQueue
	 */
	public function getQueue(int $space = 1): Queue
	{
		return $this->ffaQueue;
	}
}