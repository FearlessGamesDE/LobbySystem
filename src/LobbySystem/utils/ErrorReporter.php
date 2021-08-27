<?php

namespace LobbySystem\utils;

use LobbySystem\Loader;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class ErrorReporter
{
	/**
	 * @param string|null $path
	 */
	public static function send(string $path = null): void
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
			DiscordWebhook::send(Output::translate("errorURL"), $error, [], "", false);
		}
	}

	public static function load(): void
	{
		Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
			Server::getInstance()->getAsyncPool()->submitTask(new class(Server::getInstance()->getDataPath()) extends AsyncTask {
				/**
				 * @var string
				 */
				private $path;

				public function __construct(string $path)
				{
					$this->path = $path;
				}

				public function onRun(): void
				{
					ErrorReporter::send($this->path);
				}
			});
		}), 20 * 60);
	}
}