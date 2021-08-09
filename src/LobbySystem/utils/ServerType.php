<?php

namespace LobbySystem\utils;

use alemiz\sga\StarGateAtlantis;

class ServerType
{
	public static function get(): string
	{
		return explode("_", StarGateAtlantis::getInstance()->getClientName())[0] ?? "unknown";
	}
}