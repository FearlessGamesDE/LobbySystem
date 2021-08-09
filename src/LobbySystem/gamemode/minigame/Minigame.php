<?php

namespace LobbySystem\gamemode\minigame;

class Minigame
{
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var string
	 */
	private $name;

	/**
	 * Minigame constructor.
	 * @param string $id
	 * @param string $name
	 */
	public function __construct(string $id, string $name)
	{
		$this->id = $id;
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getDisplayName(): string
	{
		return $this->name;
	}
}