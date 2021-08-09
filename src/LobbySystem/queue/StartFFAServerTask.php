<?php

namespace LobbySystem\queue;

use alemiz\sga\StarGateAtlantis;
use Exception;
use LobbySystem\gamemode\GamemodeManager;
use LobbySystem\Loader;
use LobbySystem\utils\Generator;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use ServerHandler\Docker\DockerContainer;
use ServerHandler\Docker\DockerContainerInstance;
use ServerHandler\ServerHandler;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StartFFAServerTask extends AsyncTask
{
	private $gamemode;
	private $start;
	private $port;

	public function __construct(string $gamemode)
	{
		$this->start = microtime(true);
		$this->gamemode = $gamemode;
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
			$this->port = Generator::generateQueueId();
			$container = DockerContainer::create("pocketmine-mp", "v" . $this->gamemode)->mapPort("20" . $this->port, "19132/udp")->setVolume("/minecraft/Virtual/PocketMine-MP/plugins", "/plugins")->setVolume("/minecraft/Virtual/" . $this->gamemode, "/server")->start();
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

	public function onCompletion(Server $server): void
	{
		if ($this->getResult() instanceof DockerContainerInstance) {
			StarGateAtlantis::getInstance()->addServer("0.0.0.0", "20" . $this->port, $this->gamemode);
			GamemodeManager::getGamemode($this->gamemode)->getQueue()->setServer($this->getResult());
			Server::getInstance()->getLogger()->info("Created Container " . $this->gamemode . " on 20" . $this->port . " in " . round(microtime(true) - $this->start, 3) . "s");
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