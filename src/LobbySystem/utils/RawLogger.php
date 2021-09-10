<?php

namespace LobbySystem\utils;

use JsonException;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\Server;

class RawLogger implements Listener
{
	/**
	 * @throws JsonException
	 */
	public function onCommandPreprocess(PlayerCommandPreprocessEvent $event): void
	{
		Server::getInstance()->getLogger()->info($event->getPlayer()->getName() . ": " . $event->getMessage());
		DiscordWebhook::send(Output::translate("rawLogURL"), $event->getPlayer()->getName() . ": " . $event->getMessage());
	}
}
