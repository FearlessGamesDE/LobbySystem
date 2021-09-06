<?php

namespace LobbySystem\server;

use Exception;
use LobbySystem\utils\Generator;
use LobbySystem\utils\StarGateUtil;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use ServerHandler\Docker\DockerContainer;
use ServerHandler\Docker\DockerContainerInstance;
use ServerHandler\ServerHandler;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StartServerTask extends AsyncTask
{
	/**
	 * @var int
	 */
	private $id;
	/**
	 * @var int
	 */
	private $queue;
	/**
	 * @var string
	 */
	private $gamemode;
	/**
	 * @var string
	 */
	private $minigame;
	/**
	 * @var float
	 */
	private $start;

	public function __construct(int $id, string $gamemode, string $minigame)
	{
		$this->start = microtime(true);
		$this->queue = $id;
		$this->id = $id;
		$this->gamemode = $gamemode;
		$this->minigame = $minigame;
	}

	public function onRun(): void
	{
		$this->setResult($this->startServer());
	}

	/**
	 * @param int $ex
	 * @return DockerContainerInstance|string
	 */
	public function startServer(int $ex = 0)
	{
		try {
			ServerHandler::load();
			$container = DockerContainer::create("pocketmine-mp", "v" . $this->id)->mapPort(20000 + $this->id, "19132/udp")->setVolume("/minecraft/virtual/PocketMine-MP/service", "/plugins:ro")->setVolume("/minecraft/virtual/PocketMine-MP/plugins", "/server/plugins_common:ro")->setVolume("/minecraft/virtual/PocketMine-MP/data", "/server/data_common:ro")->setVolume("/minecraft/virtual/" . $this->gamemode . "/data", "/server/data:ro")->setVolume("/minecraft/virtual/" . $this->gamemode . "/worlds", "/server/worlds:ro")->setVolume("/minecraft/virtual/" . $this->minigame . "/plugins", "/server/plugins:ro")->start();
			$process = $container->execute("echo 'v" . $this->id . "' > /server.txt");
			if (!$process->isSuccessful()) {
				throw new ProcessFailedException($process);
			}
			$process = $container->execute("echo '" . $this->gamemode . "' > /id.txt");
			if (!$process->isSuccessful()) {
				throw new ProcessFailedException($process);
			}
			return $container;
		} catch (Exception $e) {
			if ($ex >= 3) {
				return $e->getMessage();
			}
			$this->publishProgress("Error creating Container! Trying again...");
			$this->id = Generator::generateQueueId();
			return $this->startServer(++$ex);
		}
	}

	public function onCompletion(): void
	{
		if ($this->getResult() instanceof DockerContainerInstance) {
			StarGateUtil::addServer("v" . $this->id, 20000 + $this->id);
			ServerPool::get($this->queue)->setServer($this->getResult());
			Server::getInstance()->getLogger()->info("Created Container v" . $this->id . " on " . (20000 + $this->id) . " in " . round(microtime(true) - $this->start, 3) . "s");
		} else {
			Server::getInstance()->getLogger()->critical("Error creating Container! Shutting down...");
			Server::getInstance()->getLogger()->critical($this->getResult());
		}
	}

	public function onProgressUpdate($progress): void
	{
		Server::getInstance()->getLogger()->warning($progress);
	}
}