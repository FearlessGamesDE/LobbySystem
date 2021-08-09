<?php


namespace LobbySystem\command;


use alemiz\sga\StarGateAtlantis;
use LobbySystem\packets\party\request\ChatRequestPacket;
use LobbySystem\packets\party\request\DisbandRequestPacket;
use LobbySystem\packets\party\request\InviteRequestPacket;
use LobbySystem\packets\party\request\KickRequestPacket;
use LobbySystem\packets\party\request\ListRequestPacket;
use LobbySystem\packets\party\request\PromoteRequestPacket;
use LobbySystem\packets\party\request\QuitRequestPacket;
use LobbySystem\packets\party\request\WarpRequestPacket;
use LobbySystem\utils\Output;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class PartyCommand extends Command
{
	public function __construct()
	{
		parent::__construct("party", "Manage your party", "/party", ["p", "pc"]);
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return mixed|void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if ($commandLabel === "pc") {
			array_unshift($args, "chat");
		}

		if (!$sender instanceof Player) {
			return;
		}

		switch ($args[0] ?? "help") {
			case "invite":
				/** @noinspection PhpMissingBreakStatementInspection */
			case "join":
				array_shift($args);
			default:
				if (isset($args[0])) {
					StarGateAtlantis::getInstance()->isOnline($args[0], static function (string $response) use ($sender, $args) {
						if ($response === "false") {
							Output::send($sender, "not-online", ["{player}" => $args[0]], "party-prefix");
						} else {
							$request = new InviteRequestPacket();
							$request->inviter = $sender->getName();
							$request->player = $args[0];
							StarGateAtlantis::getInstance()->forwardPacket("lobby", "default", $request);
						}
					});
					return;
				}
				break;
			case "leave":
			case "quit":
				$request = new QuitRequestPacket();
				$request->player = $sender->getName();
				StarGateAtlantis::getInstance()->forwardPacket("lobby", "default", $request);
				return;
			case "list":
				$request = new ListRequestPacket();
				$request->player = $sender->getName();
				StarGateAtlantis::getInstance()->forwardPacket("lobby", "default", $request);
				return;
			case "c":
			case "chat":
				if (isset($args[1])) {
					$request = new ChatRequestPacket();
					$request->player = $sender->getName();
					$request->message = implode(" ", array_slice($args, 1));
					StarGateAtlantis::getInstance()->forwardPacket("lobby", "default", $request);
					return;
				}
				break;
			case "warp":
				$request = new WarpRequestPacket();
				$request->player = $sender->getName();
				StarGateAtlantis::getInstance()->forwardPacket("lobby", "default", $request);
				return;
			case "promote":
				if (isset($args[1])) {
					$request = new PromoteRequestPacket();
					$request->player = $args[1];
					$request->promoter = $sender->getName();
					StarGateAtlantis::getInstance()->forwardPacket("lobby", "default", $request);
					return;
				}
				break;
			case "kick":
				if (isset($args[1])) {
					$request = new KickRequestPacket();
					$request->player = $args[1];
					$request->kicker = $sender->getName();
					StarGateAtlantis::getInstance()->forwardPacket("lobby", "default", $request);
					return;
				}
				break;
			case "disband":
				$request = new DisbandRequestPacket();
				$request->player = $sender->getName();
				StarGateAtlantis::getInstance()->forwardPacket("lobby", "default", $request);
				return;
			case "help":
				break;
		}
		Output::send($sender, "party-help", [], "party-prefix");
	}
}