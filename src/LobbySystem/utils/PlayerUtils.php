<?php

namespace LobbySystem\utils;

use pocketmine\player\Player;

class PlayerUtils
{
	/**
	 * @param Player $player
	 */
	public static function recoverPlayer(Player $player): void
	{
		$player->setHealth($player->getMaxHealth());
		$player->getHungerManager()->setFood(20);
		$player->getHungerManager()->setSaturation(20);
		$player->getHungerManager()->setExhaustion(0);
	}
}