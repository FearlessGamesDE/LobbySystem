<?php

namespace LobbySystem\utils;

use JsonException;
use LobbySystem\Loader;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class ErrorReporter
{
	/**
	 * @param string|null $path
	 * @throws JsonException
	 */
	public static function send(string $path = null, ?string $webhook = null): void
	{
		if ($path === null) {
			$path = Server::getInstance()->getDataPath();
		}
		$log = file($path . "server.log");

		if ($log === false) {
			return;
		}

		$errors = [];
		$current = false;

		foreach ($log as $line) {
			if (strpos($line, "CRITICAL") !== false) {
				if (!$current) {
					$current = true;
					$errors[] = [];
				}
				$errors[array_key_last($errors)][] = $line;
			} else {
				$current = false;
			}
		}

		$fixedErrors = [];

		foreach ($errors as $error) {

			$fixedErrors[] = "```";
			$chars = 0;

			foreach ($error as $line) {
				$chars += strlen($line) + 2;
				if ($chars > 1994) {
					$fixedErrors[array_key_last($fixedErrors)] .= "```";
					$fixedErrors[] = "```\n" . $line;
					$chars = strlen($line) + 2;
				} else {
					$fixedErrors[array_key_last($fixedErrors)] .= "\n" . $line;
				}
			}

			$fixedErrors[array_key_last($fixedErrors)] .= "```";
			$fixedErrors[] = "----------";
		}

		file_put_contents($path . "server.log", []);

		foreach ($fixedErrors as $error) {
			DiscordWebhook::send($webhook ?? Output::translate("errorURL"), $error, [], "", false);
			usleep(1000); //1ms to send in correct order
		}
	}

	public static function load(): void
	{
		Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
			Server::getInstance()->getAsyncPool()->submitTask(new class(Server::getInstance()->getDataPath(), Output::translate("errorURL"),) extends AsyncTask {
				/**
				 * @var string
				 */
				private $path;
				/**
				 * @var string
				 */
				private $webhook;

				public function __construct(string $path, string $webhook)
				{
					$this->path = $path;
					$this->webhook = $webhook;
				}

				public function onRun(): void
				{
					ErrorReporter::send($this->path, $this->webhook);
				}
			});
		}), 20 * 60);
	}
}