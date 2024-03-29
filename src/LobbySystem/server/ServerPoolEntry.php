<?php

namespace LobbySystem\server;

use LobbySystem\queue\FFAQueue;
use LobbySystem\queue\Queue;
use LobbySystem\utils\StarGateUtil;
use pocketmine\Server;
use ServerHandler\Docker\DockerContainerInstance;

class ServerPoolEntry
{
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var Queue
	 */
	private $queue;
	/**
	 * @var DockerContainerInstance
	 */
	private $instance;
	/**
	 * @var int
	 */
	private $port;
	/**
	 * @var bool
	 */
	private $isStarting = false;
	/**
	 * @var bool
	 */
	private $needStop = false;

	/**
	 * @param string $id
	 * @param Queue  $queue
	 */
	public function __construct(string $id, Queue $queue)
	{
		$this->id = $id;
		$this->queue = $queue;
	}

	public function start(): void
	{
		if (!$this->isStarting) {
			$this->isStarting = true;
			if ($this->queue instanceof FFAQueue) {
				Server::getInstance()->getAsyncPool()->submitTask(new StartFFAServerTask($this));
			} else {
				Server::getInstance()->getAsyncPool()->submitTask(new StartServerTask($this->id, $this->queue->getGamemode()->getId(), $this->queue->getGamemode()->getMinigame()->getId()));
			}
		}
	}

	/**
	 * @param DockerContainerInstance $instance
	 * @param int                     $port
	 */
	public function setServer(DockerContainerInstance $instance, int $port): void
	{
		$this->instance = $instance;
		$this->port = $port;
		ServerPool::setAddress($this->id);
		if ($this->needStop) {
			$this->instance->stop();
		}
	}

	public function serverCallback(): void
	{
		$this->isStarting = false;

		StarGateUtil::addServer($this->getServerName(), $this->port, "localhost");
		Server::getInstance()->getLogger()->info("Registered server v" . $this->id . " on " . $this->port);

		if (!$this->needStop) {
			$this->queue->ready();
		}
	}

	public function ready(): bool
	{
		if (isset($this->instance) && !$this->isStarting) {
			if ($this->instance->isRunning()) {
				return true;
			}

			$this->start();
		}
		return false;
	}

	public function stop(): void
	{
		if (isset($this->instance)) {
			$this->instance->stop();
		} else {
			$this->needStop = true;
		}
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getServerName(): string
	{
		return isset($this->instance) ? $this->instance->getName() : "null";
	}
}