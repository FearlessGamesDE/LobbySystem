<?php

namespace LobbySystem\utils;

use alemiz\sga\client\StarGateClient;
use alemiz\sga\StarGateAtlantis;

class ServerType
{
	public static function get(): string
	{
		$client = StarGateAtlantis::getInstance()->getDefaultClient();
		if ($client instanceof StarGateClient) {
			return explode("_", $client->getName())[0];
		}

		return "unknown";
	}
}