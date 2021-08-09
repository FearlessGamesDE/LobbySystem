<?php

namespace LobbySystem\queue;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class QueueHandler implements Listener
{
	public function onQuit(PlayerQuitEvent $event): void
	{
		QueueManager::remove($event->getPlayer());
	}
}