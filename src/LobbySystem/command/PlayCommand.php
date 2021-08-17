<?php

namespace LobbySystem\command;

use LobbySystem\packets\server\PlayPacket;
use LobbySystem\utils\StarGateUtil;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class PlayCommand extends Command
{
	public function __construct()
	{
		parent::__construct("play", "Play a game", "/play <gamemode>");
	}

	/**
	 * @param CommandSender $sender
	 * @param string        $commandLabel
	 * @param string[]      $args
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void
	{
		if (!$sender instanceof Player) {
			return;
		}

		if (!isset($args[0])) {
			return;
		}

		$request = new PlayPacket();
		$request->player = $sender->getName();
		$request->gamemode = $args[0];
		StarGateUtil::request($request);
	}
}