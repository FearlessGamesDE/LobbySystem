<?php

namespace LobbySystem\utils;

use JsonException;
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
		$data = [
			"browser" => $agent = StarGateUtil::getClient()->getName() . " " . Server::getInstance()->getName() . " " . Server::getInstance()->getPocketMineVersion(),
			"data" => implode(PHP_EOL, TimingsHandler::printTimings())
		];

		$host = Server::getInstance()->getConfigGroup()->getConfigString("timings.host", "timings.pmmp.io");

		try {
			$response = Internet::simpleCurl("https://$host/?upload=true", 10, [], [
				CURLOPT_HTTPHEADER => [
					"User-Agent: $agent",
					"Content-Type: application/x-www-form-urlencoded"
				],
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($data),
				CURLOPT_AUTOREFERER => false,
				CURLOPT_FOLLOWLOCATION => false
			]);
		} catch (InternetException $e) {
			Server::getInstance()->getLogger()->logException($e);
			return;
		}

		try {
			$result = json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
		}catch (JsonException $exception){
			$result = [];
		}
		if (is_array($result) && isset($result["id"])) {
			DiscordWebhook::send(Output::translate("timingURL"), "", [DiscordWebhook::buildEmbed("Timing Record", "", Color::GREEN, "https://" . $host . "/?id=" . $result["id"], DiscordWebhook::buildAuthor(Loader::getServerName()), [], [], [], [], time())]);
		} else {
			DiscordWebhook::send(Output::translate("timingURL"), "", [DiscordWebhook::buildEmbed("Timing Record Error", (string) $response->getCode(), Color::RED, "", DiscordWebhook::buildAuthor(Loader::getServerName()), [], [], [], [], time())]);
		}
	}
}