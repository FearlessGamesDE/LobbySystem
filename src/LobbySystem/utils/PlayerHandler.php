<?php

namespace LobbySystem\utils;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\utils\TextFormat;

class PlayerHandler implements Listener
{
	/**
	 * @param PlayerJoinEvent $event
	 * @priority LOW
	 */
	public function onJoin(PlayerJoinEvent $event): void
	{
		InternalInformation::constructProfileData($event->getPlayer()->getName());
		$event->getPlayer()->setNameTag(InternalInformation::getChatPrefix($event->getPlayer()->getName()) . $event->getPlayer()->getName());
	}

	/**
	 * @param PlayerChatEvent $event
	 * @priority LOW
	 */
	public function onChat(PlayerChatEvent $event): void
	{
		$event->setFormat(InternalInformation::getChatPrefix($event->getPlayer()->getName()) . "{%0} > {%1}");
	}

	/**
	 * @param PlayerCommandPreprocessEvent $event
	 * @priority LOW
	 */
	public function onCommandPreprocess(PlayerCommandPreprocessEvent $event): void
	{
		$event->setMessage(TextFormat::clean($event->getMessage()));
	}
}