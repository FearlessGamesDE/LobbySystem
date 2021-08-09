<?php

namespace LobbySystem\queue;

use alemiz\sga\StarGateAtlantis;
use Exception;
use LobbySystem\Loader;
use LobbySystem\utils\Generator;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use ServerHandler\Docker\DockerContainer;
use ServerHandler\Docker\DockerContainerInstance;
use ServerHandler\ServerHandler;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StartServerTask extends AsyncTask
{
	private $id;
	private $queue;
	private $gamemode;
	private $minigame;
	private $start;

	public function __construct(string $id, string $gamemode, string $minigame)
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
	 * @return DockerContainerInstance|Exception
	 */
	public function startServer($ex = 0)
	{
		try {
			ServerHandler::load();
			$container = DockerContainer::create("pocketmine-mp", "v" . $this->id)->mapPort("20" . $this->id, "19132/udp")->setVolume("/minecraft/Virtual/PocketMine-MP/plugins", "/plugins")->setVolume("/minecraft/Virtual/" . $this->gamemode . "/data", "/server/data")->setVolume("/minecraft/Virtual/" . $this->gamemode . "/worlds", "/server/worlds")->setVolume("/minecraft/Virtual/" . $this->minigame . "/plugins", "/server/plugins")->start();
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
				return $e;
			}
			$this->publishProgress("Error creating Container! Trying again...");
			$this->id = Generator::generateQueueId();
			return $this->startServer(++$ex);
		}
	}

	public function onCompletion(Server $server): void
	{
		if ($this->getResult() instanceof DockerContainerInstance) {
			StarGateAtlantis::getInstance()->addServer("0.0.0.0", "20" . $this->id, "v" . $this->id);
			QueueManager::get($this->queue)->setServer($this->getResult());
			Server::getInstance()->getLogger()->info("Created Container v" . $this->id . " on 20" . $this->id . " in " . round(microtime(true) - $this->start, 3) . "s");
		} elseif ($this->getResult() instanceof Exception) {
			Server::getInstance()->getLogger()->critical("Error creating Container! Shutting down...");
			Server::getInstance()->getLogger()->logException($this->getResult());
			Loader::getInstance()->setEnabled(false);
		}
	}

	public function onProgressUpdate(Server $server, $progress): void
	{
		Server::getInstance()->getLogger()->warning($progress);
	}
}