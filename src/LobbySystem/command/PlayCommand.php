<?php

namespace LobbySystem\command;

use alemiz\sga\StarGateAtlantis;
use LobbySystem\packets\PacketHandler;
use LobbySystem\packets\server\PlayPacket;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class PlayCommand extends Command
{
	public function __construct()
	{
		parent::__construct("play", "Play a game", "/play <gamemode>");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$sender instanceof Player) {
			return;
		}

		if (empty($args[0])) {
			return;
		}

		$request = new PlayPacket();
		$request->player = $sender->getName();
		$request->gamemode = $args[0];
		if (StarGateAtlantis::getInstance()->getClientName() === "lobby") {
			PacketHandler::handle($request);
		}else{
			StarGateAtlantis::getInstance()->forwardPacket("lobby", "default", $request);
		}
	}
}