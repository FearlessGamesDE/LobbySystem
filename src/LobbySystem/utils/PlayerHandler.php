<?php

namespace LobbySystem\utils;

use LobbySystem\Loader;
use LobbySystem\packets\server\RejoinInformationPacket;
use LobbySystem\server\ReconnectHandler;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
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

		if (ReconnectHandler::hasLocation($event->getPlayer())) {
			StarGateUtil::transferPlayer($event->getPlayer()->getName(), ReconnectHandler::getLocation($event->getPlayer()));
		}
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

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function onQuit(PlayerQuitEvent $event): void
	{
		if (!Loader::isMaster()) {
			$pk = new RejoinInformationPacket();
			$pk->player = $event->getPlayer()->getName();
			StarGateUtil::request($pk);
		}
	}
}