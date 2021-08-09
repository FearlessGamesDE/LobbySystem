<?php

namespace LobbySystem\gamemode;

use LobbySystem\gamemode\minigame\Minigame;
use LobbySystem\queue\Queue;
use LobbySystem\queue\QueueManager;
use LobbySystem\utils\Generator;
use UnexpectedValueException;

class Gamemode
{
	/**
	 * @var Minigame
	 */
	private $minigame;
	/**
	 * @var int
	 */
	private $capacity;
	/**
	 * @var int
	 */
	private $minimum;
	/**
	 * @var Queue[]
	 */
	private $queues = [];
	/**
	 * @var string
	 */
	private $name;

	/**
	 * Gamemode constructor.
	 * @param Minigame $minigame
	 * @param string $name
	 * @param int $capacity
	 * @param int $minimum
	 */
	public function __construct(Minigame $minigame, string $name, int $capacity, int $minimum = 2)
	{
		$this->minigame = $minigame;
		$this->capacity = $capacity;
		$this->minimum = $minimum;
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return implode("_", [$this->minigame->getId(), Generator::toString($this->capacity)]);
	}

	/**
	 * @return int
	 */
	public function getCapacity(): int
	{
		return $this->capacity;
	}

	/**
	 * @return int
	 */
	public function getMinimum(): int
	{
		return $this->minimum;
	}

	/**
	 * @return Minigame
	 */
	public function getMinigame(): Minigame
	{
		return $this->minigame;
	}

	/**
	 * @param int $space
	 * @return Queue
	 */
	public function getQueue(int $space = 1): Queue
	{
		if ($space > $this->getCapacity()) {
			throw new UnexpectedValueException("Tried to open a Queue with " . $space . "/" . $this->getCapacity() . " members");
		}
		foreach ($this->queues as $queue) {
			if ($queue->getSize() + $space <= $this->getCapacity()) {
				return $queue;
			}
		}
		QueueManager::open($this);
		return $this->getQueue();
	}

	/**
	 * @param Queue $queue
	 */
	public function addQueue(Queue $queue): void
	{
		$this->queues[] = $queue;
	}

	/**
	 * @param Queue $queue
	 */
	public function removeQueue(Queue $queue): void
	{
		unset($this->queues[array_search($queue, $this->queues)]);
	}

	/**
	 * @return string
	 */
	public function getDisplayName(): string
	{
		return $this->name;
	}
}