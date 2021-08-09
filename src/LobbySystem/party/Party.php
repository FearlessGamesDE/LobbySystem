<?php

namespace LobbySystem\party;

use alemiz\sga\StarGateAtlantis;
use LobbySystem\Loader;
use LobbySystem\packets\party\info\DisbandPacket;
use LobbySystem\packets\party\info\InviteExpirePacket;
use LobbySystem\packets\party\info\InvitePacket;
use LobbySystem\packets\party\info\PartyJoinPacket;
use LobbySystem\packets\party\info\PromotePacket;
use LobbySystem\packets\party\info\PartyQuitPacket;
use LobbySystem\utils\StarGateUtil;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;

class Party
{
	/**
	 * @var string
	 */
	private $owner;
	/**
	 * @var array<string, string>
	 */
	private $moderators = [];
	/**
	 * @var array<string, string>
	 */
	private $members = [];
	/**
	 * @var array<string, string>
	 */
	private $invites = [];
	/**
	 * @var TaskHandler[]
	 */
	public $offline = [];

	/**
	 * Party constructor.
	 * @param string $owner
	 */
	public function __construct(string $owner)
	{
		$this->owner = strtolower($owner);
	}

	/**
	 * @param string $player
	 */
	public function add(string $player): void
	{
		$info = new PartyJoinPacket();
		$info->player = $player;
		$info->party = $this->getContents();
		StarGateUtil::distribute($info);
		$this->members[$player] = $player;
		unset($this->invites[$player]);
	}

	/**
	 * @param string $player
	 * @param bool   $isKick
	 */
	public function remove(string $player, bool $isKick = true): void
	{
		if ($this->owner === $player) {
			if ($this->getSize() <= 2) {
				$this->disband();
				return;
			}
			if ($this->moderators !== []) {
				$this->promote(array_rand($this->moderators));
			} else {
				$p = array_rand($this->members);
				$this->promote($p);
				$this->promote($p);
			}
		}
		unset($this->members[$player], $this->moderators[$player], $this->offline[$player]);
		$info = new PartyQuitPacket();
		$info->player = $player;
		$info->kick = $isKick;
		$info->party = $this->getContents();
		StarGateUtil::distribute($info);
	}

	/**
	 * @param string $player
	 */
	public function promote(string $player): void
	{
		if (isset($this->members[$player])) {
			unset($this->members[$player]);
			$this->moderators[$player] = $player;
			$info = new PromotePacket();
			$info->player = $player;
			$info->moderator = true;
			$info->party = $this->getContents();
			StarGateUtil::distribute($info);
		} elseif (isset($this->moderators[$player])) {
			PartyManager::remove($this);
			$this->moderators[$this->owner] = $this->owner;
			$this->owner = $player;
			unset($this->moderators[$player]);
			PartyManager::add($this);
			$info = new PromotePacket();
			$info->player = $player;
			$info->moderator = false;
			$info->party = $this->getContents();
			StarGateUtil::distribute($info);
		}
	}

	public function disband(): void
	{
		PartyManager::remove($this);
		$info = new DisbandPacket();
		$info->party = $this->getContents();
		StarGateUtil::distribute($info);
	}

	/**
	 * @param string $player
	 */
	public function invite(string $player): void
	{
		PartyManager::add($this);
		$this->invites[$player] = $player;
		$info = new InvitePacket();
		$info->player = $player;
		$info->party = $this->getContents();
		StarGateUtil::distribute($info);
		Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
			if (isset($this->invites[$player])) {
				unset($this->invites[$player]);
				$info = new InviteExpirePacket();
				$info->player = $player;
				$info->party = $this->getContents();
				StarGateUtil::distribute($info);
			}
			if (!$this->isValid() && count($this->invites) < 1) {
				PartyManager::remove($this);
			}
		}), 20 * 60);
	}

	/**
	 * @param string $player
	 * @return bool
	 */
	public function contains(string $player): bool
	{
		return isset($this->members[$player]) or isset($this->moderators[$player]) or $this->owner === $player;
	}

	/**
	 * @return int
	 */
	public function getSize(): int
	{
		return count($this->moderators) + count($this->members) + (isset($this->owner) ? 1 : 0);
	}

	/**
	 * @return bool
	 */
	public function isValid(): bool
	{
		return $this->getSize() > 1 or $this->invites !== [];
	}

	/**
	 * @return string
	 */
	public function getOwner(): string
	{
		return $this->owner;
	}

	/**
	 * @return string[]
	 */
	public function getModerators(): array
	{
		return array_values($this->moderators);
	}

	/**
	 * @return string[]
	 */
	public function getMembers(): array
	{
		return array_values($this->members);
	}

	/**
	 * @return string[]
	 */
	public function getContents(): array
	{
		return array_merge([$this->getOwner()], $this->getModerators(), $this->getMembers());
	}

	/**
	 * @param string $player
	 * @return bool
	 */
	public function isInvited(string $player): bool
	{
		return isset($this->invites[$player]);
	}
}