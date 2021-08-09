<?php

namespace LobbySystem\command;

use alemiz\sga\StarGateAtlantis;
use pocketmine\Server;

class CommandManager
{
	public static function load(): void
	{
		Server::getInstance()->getCommandMap()->registerAll("LobbySystem", [
			new PartyCommand(),
			new PlayCommand()
		]);
		if (StarGateAtlantis::getInstance()->getClientName() === "lobby") {
			Server::getInstance()->getCommandMap()->registerAll("LobbySystem", [
				new LeaveCommand(),
				new StartCommand()
			]);
		}
	}
}