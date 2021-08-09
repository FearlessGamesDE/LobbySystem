<?php

namespace LobbySystem\utils;

use alemiz\sga\StarGateAtlantis;
use LobbySystem\Loader;
use pocketmine\Server;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;

class TimingManager
{
	public static function load(): void
	{
		TimingsHandler::setEnabled();
	}

	public static function send(): void
	{
		$fileTimings = fopen("php://temp", "r+b");

		TimingsHandler::printTimings($fileTimings);

		fseek($fileTimings, 0);
		$data = stream_get_contents($fileTimings);
		fclose($fileTimings);

		$host = Server::getInstance()->getProperty("timings.host", "timings.aikar.co");

		try {
			$result = Internet::simpleCurl("https://$host/post", 10, [], [
				CURLOPT_HTTPHEADER => [
					"User-Agent: Spigot/" . Server::getInstance()->getName() . "/" . Server::getInstance()->getPocketMineVersion(), //Spigot just allows access
					"Content-Type: application/x-www-form-urlencoded"
				],
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => gzencode($data),
				CURLOPT_AUTOREFERER => false,
				CURLOPT_FOLLOWLOCATION => false
			]);
		} catch (InternetException $e) {
			Server::getInstance()->getLogger()->logException($e);
			return;
		}

		if (isset($result[1][0]["location"])) {
			DiscordWebhook::send(Output::translate("timingURL"), "", [DiscordWebhook::buildEmbed("Timing Record", "", Color::GREEN, $result[1][0]["location"], DiscordWebhook::buildAuthor(Loader::$serverName), [], [], [], [], time())]);
		} else {
			DiscordWebhook::send(Output::translate("timingURL"), "", [DiscordWebhook::buildEmbed("Timing Record Error", $result[0] ?? "", Color::RED, "", DiscordWebhook::buildAuthor(Loader::$serverName), [], [], [], [], time())]);
		}
	}
}