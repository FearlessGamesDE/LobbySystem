<?php

namespace LobbySystem\packets;

use Exception;
use LobbySystem\gamemode\FreeGamemode;
use LobbySystem\gamemode\GamemodeManager;
use LobbySystem\Loader;
use LobbySystem\packets\party\info\ChatPacket;
use LobbySystem\packets\party\info\DisbandPacket;
use LobbySystem\packets\party\info\InPartyPacket;
use LobbySystem\packets\party\info\InviteExpirePacket;
use LobbySystem\packets\party\info\InvitePacket;
use LobbySystem\packets\party\info\NoPermissionOwnerPacket;
use LobbySystem\packets\party\info\PartyJoinPacket;
use LobbySystem\packets\party\info\ListPacket;
use LobbySystem\packets\party\info\NoPermissionModeratorPacket;
use LobbySystem\packets\party\info\NotInPartyPacket;
use LobbySystem\packets\party\info\PromotePacket;
use LobbySystem\packets\party\info\PartyQuitPacket;
use LobbySystem\packets\party\info\WarpPacket;
use LobbySystem\packets\party\request\ChatRequestPacket;
use LobbySystem\packets\party\request\DisbandRequestPacket;
use LobbySystem\packets\party\request\InviteRequestPacket;
use LobbySystem\packets\party\request\KickRequestPacket;
use LobbySystem\packets\party\request\ListRequestPacket;
use LobbySystem\packets\party\request\PromoteRequestPacket;
use LobbySystem\packets\party\request\QuitRequestPacket;
use LobbySystem\packets\party\request\WarpRequestPacket;
use LobbySystem\packets\server\DisablePacket;
use LobbySystem\packets\server\EnablePacket;
use LobbySystem\packets\server\PlayPacket;
use LobbySystem\packets\server\InitializePacket;
use LobbySystem\packets\server\ReadyPacket;
use LobbySystem\party\PartyManager;
use LobbySystem\queue\QueueManager;
use LobbySystem\server\ServerPool;
use LobbySystem\server\VirtualServer;
use LobbySystem\utils\Output;
use LobbySystem\utils\PlayerCache;
use LobbySystem\utils\StarGateUtil;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class PacketHandler implements Listener
{
	public static function load(): void
	{
		foreach (
			[
				new EnablePacket(),
				new DisablePacket(),
				new PlayPacket(),
				new InitializePacket(),
				new ReadyPacket(),
				new InviteRequestPacket(),
				new InviteExpirePacket(),
				new InvitePacket(),
				new NotInPartyPacket(),
				new InPartyPacket(),
				new PartyJoinPacket(),
				new PartyQuitPacket(),
				new ListRequestPacket(),
				new ListPacket(),
				new NoPermissionModeratorPacket(),
				new NoPermissionOwnerPacket(),
				new DisbandRequestPacket(),
				new DisbandPacket(),
				new QuitRequestPacket(),
				new PromoteRequestPacket(),
				new PromotePacket(),
				new KickRequestPacket(),
				new WarpRequestPacket(),
				new WarpPacket(),
				new ChatRequestPacket(),
				new ChatPacket()
			] as $packet) {
			StarGateUtil::getClient()->getProtocolCodec()->registerPacket($packet->getPacketID(), $packet);
		}

		Loader::getInstance()->getServer()->getPluginManager()->registerEvents(new self(), Loader::getInstance());
	}

	/**
	 * @param NetworkPacket $packet
	 * @return bool
	 */
	public static function handle(NetworkPacket $packet): bool
	{
		switch ($packet->getPacketId()) {
			case PacketPool::SERVER_ENABLE:
				/** @var EnablePacket $packet */
				Output::important(Server::getInstance()->getOnlinePlayers(), "enable");
				break;
			case PacketPool::SERVER_DISABLE:
				/** @var DisablePacket $packet */
				Output::important(Server::getInstance()->getOnlinePlayers(), "disable");
				break;
			case PacketPool::SERVER_PLAY:
				/** @var PlayPacket $packet */
				try {
					$gamemode = GamemodeManager::getGamemode($packet->gamemode);
				} catch (Exception $exception) {
					break;
				}

				if (($player = Server::getInstance()->getPlayerExact($packet->player)) instanceof Player) {
					QueueManager::add($player, $gamemode);
				} elseif ($gamemode instanceof FreeGamemode) {
					StarGateUtil::transferPlayer($packet->player, $gamemode->getId());
				} else {
					StarGateUtil::transferPlayer($packet->player, "lobby");
					Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(static function () use ($packet, $gamemode): void {
						if (($player = Server::getInstance()->getPlayerExact($packet->player)) instanceof Player) {
							QueueManager::add($player, $gamemode);
						}
					}), 20 * 5);
				}
				break;
			case PacketPool::SERVER_INITIALIZE: //TODO
				/** @var InitializePacket $packet */
				VirtualServer::init($packet);
				break;
			case PacketPool::SERVER_READY:
				/** @var ReadyPacket $packet */
				ServerPool::getAddress($packet->serverName)->serverCallback();
				break;
			case PacketPool::PARTY_REQUEST_INVITE:
				/** @var InviteRequestPacket $packet */
				$party = PartyManager::get($packet->inviter);
				if (!$party->isValid()) {
					$inviter = PartyManager::get($packet->player);
					if ($inviter->isInvited($packet->inviter)) {
						$inviter->add($packet->inviter);
						break;
					}
				}
				if ($party->getOwner() === $packet->inviter || in_array($packet->inviter, $party->getModerators(), true)) {
					$new = PartyManager::get($packet->player);
					if ($new->isValid()) {
						$info = new InPartyPacket();
						$info->player = $packet->player;
						$info->inviter = $packet->inviter;
						StarGateUtil::sendTo($packet->from, $info);
					} elseif ($packet->player !== $packet->inviter && !$party->isInvited($packet->player)) {
						$party->invite($packet->player);
					}
				} else {
					$info = new NoPermissionModeratorPacket();
					$info->player = $packet->inviter;
					StarGateUtil::sendTo($packet->from, $info);
				}
				break;
			case PacketPool::PARTY_INFO_EXPIRE:
				/** @var InviteExpirePacket $packet */
				Output::important($packet->party, "party-invite-expire-other", ["{player}" => $packet->player]);
				Output::important($packet->player, "party-invite-expire", ["{player}" => $packet->party[0]]);
				break;
			case PacketPool::PARTY_INFO_INVITE:
				/** @var InvitePacket $packet */
				Output::important($packet->party, "party-invite-other", ["{player}" => $packet->player]);
				Output::important($packet->player, "party-invite", ["{player}" => $packet->party[0]]);
				break;
			case PacketPool::PARTY_INFO_NOT_IN_PARTY:
				/** @var NotInPartyPacket $packet */
				Output::send($packet->player, "not-in-party", [], "party-prefix");
				break;
			case PacketPool::PARTY_INFO_IN_PARTY:
				/** @var InPartyPacket $packet */
				Output::send($packet->inviter, "in-party", ["{player}" => $packet->player], "party-prefix");
				break;
			case PacketPool::PARTY_INFO_JOIN:
				/** @var PartyJoinPacket $packet */
				Output::important($packet->party, "party-join-other", ["{player}" => $packet->player]);
				Output::important($packet->player, "party-join", ["{player}" => $packet->party[0]]);
				break;
			case PacketPool::PARTY_INFO_QUIT:
				/** @var PartyQuitPacket $packet */
				Output::important($packet->party, $packet->kick ? "party-kick-other" : "party-quit-other", ["{player}" => $packet->player]);
				Output::important($packet->player, $packet->kick ? "party-kick" : "party-quit", ["{player}" => $packet->party[0]]);
				break;
			case PacketPool::PARTY_REQUEST_LIST:
				/** @var ListRequestPacket $packet */
				$party = PartyManager::get($packet->player);
				if ($party->isValid()) {
					$info = new ListPacket();
					$info->player = $packet->player;
					$info->owner = $party->getOwner();
					$info->moderators = $party->getModerators();
					$info->members = $party->getMembers();
					foreach ($party->getContents() as $player) {
						$info->online[$player] = PlayerCache::isOnline($player);
					}
				} else {
					$info = new NotInPartyPacket();
					$info->player = $packet->player;
				}
				StarGateUtil::sendTo($packet->from, $info);
				break;
			case PacketPool::PARTY_INFO_LIST:
				/** @var ListPacket $packet */
				$owner = Output::replace($packet->online[$packet->owner] ? "online" : "offline", ["{player}" => $packet->owner]);
				$moderators = array_map(static function ($player) use ($packet): string {
					return Output::replace($packet->online[$player] ? "online" : "offline", ["{player}" => $player]);
				}, $packet->moderators);
				$members = array_map(static function ($player) use ($packet): string {
					return Output::replace($packet->online[$player] ? "online" : "offline", ["{player}" => $player]);
				}, $packet->members);
				Output::send($packet->player, "party-list", ["{owner}" => $owner, "{moderators}" => implode(", ", $moderators), "{members}" => implode(", ", $members)], "party-prefix");
				break;
			case PacketPool::PARTY_INFO_NOPERMISSION_MODERATOR:
				/** @var NoPermissionModeratorPacket $packet */
				Output::send($packet->player, "party-nopermission-moderator", [], "party-prefix");
				break;
			case PacketPool::PARTY_INFO_NOPERMISSION_OWNER:
				/** @var NoPermissionOwnerPacket $packet */
				Output::send($packet->player, "party-nopermission-owner", [], "party-prefix");
				break;
			case PacketPool::PARTY_REQUEST_DISBAND:
				/** @var DisbandRequestPacket $packet */
				$party = PartyManager::get($packet->player);
				if (!$party->isValid()) {
					$info = new NotInPartyPacket();
					$info->player = $packet->player;
					StarGateUtil::sendTo($packet->from, $info);
				} elseif ($party->getOwner() === $packet->player) {
					$party->disband();
				} else {
					$info = new NoPermissionOwnerPacket();
					$info->player = $packet->player;
					StarGateUtil::sendTo($packet->from, $info);
				}
				break;
			case PacketPool::PARTY_INFO_DISBAND:
				/** @var DisbandPacket $packet */
				Output::important($packet->party, "party-disband");
				break;
			case PacketPool::PARTY_REQUEST_QUIT:
				/** @var QuitRequestPacket $packet */
				$party = PartyManager::get($packet->player);
				if (!$party->isValid()) {
					$info = new NotInPartyPacket();
					$info->player = $packet->player;
					StarGateUtil::sendTo($packet->from, $info);
				} else {
					$party->remove($packet->player, false);
				}
				break;
			case PacketPool::PARTY_REQUEST_PROMOTE:
				/** @var PromoteRequestPacket $packet */
				$party = PartyManager::get($packet->promoter);
				if (!$party->isValid()) {
					$info = new NotInPartyPacket();
					$info->player = $packet->promoter;
					StarGateUtil::sendTo($packet->from, $info);
				} elseif ($party->getOwner() === $packet->promoter) {
					if ($party->contains($packet->player)) {
						$party->promote($packet->player);
					} else {
						$info = new NotInPartyPacket();
						$info->player = $packet->promoter;
						StarGateUtil::sendTo($packet->from, $info);
					}
				} else {
					$info = new NoPermissionOwnerPacket();
					$info->player = $packet->promoter;
					StarGateUtil::sendTo($packet->from, $info);
				}
				break;
			case PacketPool::PARTY_INFO_PROMOTE:
				/** @var PromotePacket $packet */
				Output::important($packet->party, $packet->moderator ? "party-promote-moderator" : "party-promote-owner", ["{player}" => $packet->player]);
				break;
			case PacketPool::PARTY_REQUEST_KICK:
				/** @var KickRequestPacket $packet */
				$party = PartyManager::get($packet->kicker);
				if (!$party->isValid()) {
					$info = new NotInPartyPacket();
					$info->player = $packet->kicker;
					StarGateUtil::sendTo($packet->from, $info);
				} elseif ($party->getOwner() === $packet->kicker) {
					if ($party->contains($packet->player)) {
						$party->remove($packet->player);
					} else {
						$info = new NotInPartyPacket();
						$info->player = $packet->kicker;
						StarGateUtil::sendTo($packet->from, $info);
					}
				} else {
					$info = new NoPermissionOwnerPacket();
					$info->player = $packet->kicker;
					StarGateUtil::sendTo($packet->from, $info);
				}
				break;
			case PacketPool::PARTY_REQUEST_WARP:
				/** @var WarpRequestPacket $packet */
				$party = PartyManager::get($packet->player);
				if (!$party->isValid()) {
					$info = new NotInPartyPacket();
					$info->player = $packet->player;
				} elseif ($party->getOwner() === $packet->player) {
					foreach ($party->getContents() as $player) {
						StarGateUtil::transferPlayer($player, $packet->from);
					}
					$info = new WarpPacket();
					$info->party = $party->getContents();
				} else {
					$info = new NoPermissionOwnerPacket();
					$info->player = $packet->player;
				}
				StarGateUtil::sendTo($packet->from, $info);
				break;
			case PacketPool::PARTY_INFO_WARP:
				/** @var WarpPacket $packet */
				Output::important($packet->party, "party-warp");
				break;
			case PacketPool::PARTY_REQUEST_CHAT:
				/** @var ChatRequestPacket $packet */
				$party = PartyManager::get($packet->player);
				if (!$party->isValid()) {
					$info = new NotInPartyPacket();
					$info->player = $packet->player;
					StarGateUtil::sendTo($packet->from, $info);
				} else {
					$info = new ChatPacket();
					$info->player = $packet->player;
					$info->message = $packet->message;
					$info->party = $party->getContents();
					StarGateUtil::distribute($info);
				}
				break;
			case PacketPool::PARTY_INFO_CHAT:
				/** @var ChatPacket $packet */
				Output::send($packet->party, "party-chat", ["{player}" => $packet->player, "{message}" => $packet->message], "party-prefix");
				break;
			default:
				return false;
		}
		return true;
	}
}