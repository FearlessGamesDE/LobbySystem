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
	 * @return DockerContainerInstance|Exception
	 */
	public function startServer(int $ex = 0)
	{
		try {
			ServerHandler::load();
			$this->port = Generator::generateQueueId();
			$container = DockerContainer::create("pocketmine-mp", "v" . $this->gamemode)->mapPort(20000 + $this->port, "19132/udp")->setVolume("/minecraft/Virtual/PocketMine-MP/plugins", "/plugins")->setVolume("/minecraft/virtual/PocketMine-MP/data", "/data/plugin_data")->setVolume("/minecraft/Virtual/" . $this->gamemode, "/server")->start();
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
				return $e;
			}
			$this->publishProgress("Error creating Container! Trying again...");
			return $this->startServer(++$ex);
		}
	}

	public function onCompletion(): void
	{
		if ($this->getResult() instanceof DockerContainerInstance) {
			StarGateUtil::addServer($this->gamemode, 20000 + $this->port);
			ServerPool::get($this->gamemode)->setServer($this->getResult());
			Server::getInstance()->getLogger()->info("Created Container " . $this->gamemode . " on 20" . $this->port . " in " . round(microtime(true) - $this->start, 3) . "s");
		} elseif ($this->getResult() instanceof Exception) {
			Server::getInstance()->getLogger()->critical("Error creating Container! Shutting down...");
			Server::getInstance()->getLogger()->logException($this->getResult());
		}
	}

	public function onProgressUpdate($progress): void
	{
		Server::getInstance()->getLogger()->warning($progress);
	}
}