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

class StartFFAServerTask extends AsyncTask
{
	/**
	 * @var string
	 */
	private $gamemode;
	/**
	 * @var float
	 */
	private $start;
	/**
	 * @var int
	 */
	private $port;

	public function __construct(ServerPoolEntry $entry)
	{
		$this->start = microtime(true);
		$this->gamemode = $entry->getId();
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
			$this->port = Generator::generateQueueId();
			$container = DockerContainer::create("pocketmine-mp", "v" . $this->gamemode)->mapPort(20000 + $this->port, "19132/udp")->setVolume("/minecraft/virtual/PocketMine-MP/service", "/plugins:ro")->setVolume("/minecraft/virtual/PocketMine-MP/plugins", "/server/plugins_common:ro")->setVolume("/minecraft/virtual/PocketMine-MP/data", "/server/data_common:ro")->setVolume("/minecraft/virtual/" . $this->gamemode . "/data", "/server/data:ro")->setVolume("/minecraft/virtual/" . $this->gamemode . "/worlds", "/server/worlds:ro")->setVolume("/minecraft/virtual/" . $this->gamemode . "/plugins", "/server/plugins:ro")->start();
			$process = $container->execute("echo '" . $this->gamemode . "' > /server.txt");
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
			return $this->startServer(++$ex);
		}
	}

	public function onCompletion(): void
	{
		if ($this->getResult() instanceof DockerContainerInstance) {
			ServerPool::get($this->gamemode)->setServer($this->getResult(), 20000 + $this->port);
			Server::getInstance()->getLogger()->info("Created Container " . $this->gamemode . " on 20" . $this->port . " in " . round(microtime(true) - $this->start, 3) . "s");
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