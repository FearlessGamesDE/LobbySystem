<?php

namespace LobbySystem\area;

use LobbySystem\utils\LobbyWorld;
use LobbySystem\utils\PlayerUtils;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\Player;

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
					$event->cancel();
				}
			} elseif (!$to && $from) {
				if ($area->onLeave($event->getPlayer())) {
					$area->removePlayer($event->getPlayer());
					PlayerUtils::recoverPlayer($event->getPlayer());
					//TODO: send inv
				} else {
					$event->cancel();
				}
			}
		}
	}

	/**
	 * @param EntityTeleportEvent $event
	 */
	public function onTeleport(EntityTeleportEvent $event): void
	{
		$player = $event->getEntity();
		if ($player instanceof Player) {
			foreach (AreaManager::getAreas() as $area) {
				$from = $area->getBoundingBox()->isVectorInside($event->getFrom());
				$to = $area->getBoundingBox()->isVectorInside($event->getTo());
				if ($to && !$from) {
					if ($area->onEnter($player)) {
						$area->addPlayer($player);
					} else {
						$event->cancel();
					}
				} elseif (!$to && $from) {
					if ($area->onLeave($player)) {
						$area->removePlayer($player);
						PlayerUtils::recoverPlayer($player);
						//TODO: send inv
					} else {
						$event->cancel();
					}
				}
			}
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function onPlace(BlockPlaceEvent $event): void
	{
		foreach (AreaManager::getAreas() as $area) {
			if ($area->containsPlayer($event->getPlayer()) && $area->getBoundingBox()->isVectorInside($event->getBlock()->getPos())) {
				if (!$area->onPlace($event->getPlayer(), $event->getBlock())) {
					$event->cancel();
				}
				return;
			}
		}

		$event->cancel();
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function onBreak(BlockBreakEvent $event): void
	{
		foreach (AreaManager::getAreas() as $area) {
			if ($area->containsPlayer($event->getPlayer()) && $area->getBoundingBox()->isVectorInside($event->getBlock()->getPos())) {
				if (!$area->onBreak($event->getPlayer(), $event->getBlock())) {
					$event->cancel();
				}
				return;
			}
		}

		$event->cancel();
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function onDamage(EntityDamageEvent $event): void
	{
		$player = $event->getEntity();
		if ($player instanceof Player) {
			if ($event->getCause() === EntityDamageEvent::CAUSE_VOID || $event->getFinalDamage() >= $player->getHealth()) {
				$event->cancel();
				PlayerUtils::recoverPlayer($player);
				foreach (AreaManager::getAreas() as $area) {
					if ($area->containsPlayer($player)) {
						if ($area->onDeath($player)) {
							$player->teleport($area->getSpawn());
						} else {
							$player->teleport(LobbyWorld::get()->getSafeSpawn());
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
						$event->cancel();
					}
					return;
				}
			}
		}

		$event->cancel();
	}

	/**
	 * @param PlayerExhaustEvent $event
	 */
	public function onExhaust(PlayerExhaustEvent $event): void
	{
		$player = $event->getPlayer();
		if ($player instanceof Player) {
			foreach (AreaManager::getAreas() as $area) {
				if ($area->containsPlayer($player) && !$area->onExhaust($player)) {
					$event->cancel();
				}

			}

			$event->cancel();
		}
	}
}