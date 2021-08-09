<?php

namespace LobbySystem\area;

use LobbySystem\utils\LobbyLevel;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\Player;

class AreaListener implements Listener
{
	/**
	 * @param PlayerMoveEvent $event
	 */
	public function onMove(PlayerMoveEvent $event): void
	{
		foreach (AreaManager::getAreas() as $area) {
			$from = $area->getBoundingBox()->isVectorInside($event->getFrom());
			$to = $area->getBoundingBox()->isVectorInside($event->getTo());
			if ($to && !$from) {
				if ($area->onEnter($event->getPlayer())) {
					$area->addPlayer($event->getPlayer());
				} else {
					$event->setCancelled();
				}
				return;
			}

			if (!$to && $from) {
				if ($area->onLeave($event->getPlayer())) {
					$area->removePlayer($event->getPlayer());
					//TODO: send inv
				} else {
					$event->setCancelled();
				}
				return;
			}
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function onPlace(BlockPlaceEvent $event): void
	{
		foreach (AreaManager::getAreas() as $area) {
			if ($area->containsPlayer($event->getPlayer()) && $area->getBoundingBox()->isVectorInside($event->getBlock())) {
				if (!$area->onPlace($event->getPlayer(), $event->getBlock())) {
					$event->setCancelled();
				}
				return;
			}
		}

		$event->setCancelled();
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function onBreak(BlockBreakEvent $event): void
	{
		foreach (AreaManager::getAreas() as $area) {
			if ($area->containsPlayer($event->getPlayer()) && $area->getBoundingBox()->isVectorInside($event->getBlock())) {
				if (!$area->onBreak($event->getPlayer(), $event->getBlock())) {
					$event->setCancelled();
				}
				return;
			}
		}

		$event->setCancelled();
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function onDamage(EntityDamageEvent $event): void
	{
		$player = $event->getEntity();
		if ($player instanceof Player) {
			if($event->getCause() === EntityDamageEvent::CAUSE_VOID || $event->getFinalDamage() >= $player->getHealth()){
				$event->setCancelled();
				foreach (AreaManager::getAreas() as $area) {
					if ($area->containsPlayer($player)) {
						if ($area->onDeath($player)) {
							$player->teleport($area->getSpawn());
						} else {
							$area->removePlayer($player);
							$player->teleport(LobbyLevel::get()->getSafeSpawn());
							//TODO: send inv
						}
						return;
					}
				}
				return;
			}
			foreach (AreaManager::getAreas() as $area) {
				if ($area->containsPlayer($player)) {
					if (!$area->onDamage($event->getCause(), $player, $event instanceof EntityDamageByEntityEvent ? $event->getDamager() : null)) {
						$event->setCancelled();
					}
					return;
				}
			}
		}

		$event->setCancelled();
	}
}