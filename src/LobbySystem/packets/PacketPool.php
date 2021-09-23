<?php

namespace LobbySystem\packets;

class PacketPool
{
	/**
	 * Server 0x110 - 0x11F
	 */

	public const SERVER_ENABLE = 0x10;
	public const SERVER_DISABLE = 0x11;
	public const SERVER_DATA = 0x13; //TODO
	public const SERVER_PLAY = 0x14;
	public const SERVER_INITIALIZE = 0x15;
	public const SERVER_READY = 0x16;
	public const SERVER_REJOIN_INFORMATION = 0x17;

	/**
	 * Party 0x120 - 0x14F
	 */

	public const PARTY_REQUEST_INVITE = 0x20;
	public const PARTY_INFO_EXPIRE = 0x21;
	public const PARTY_INFO_INVITE = 0x22;
	public const PARTY_INFO_NOT_IN_PARTY = 0x24;
	public const PARTY_INFO_IN_PARTY = 0x25;
	public const PARTY_INFO_JOIN = 0x26;
	public const PARTY_INFO_QUIT = 0x27;
	public const PARTY_REQUEST_LIST = 0x28;
	public const PARTY_INFO_LIST = 0x29;
	public const PARTY_INFO_NOPERMISSION_MODERATOR = 0x2A;
	public const PARTY_INFO_NOPERMISSION_OWNER = 0x2B;
	public const PARTY_REQUEST_DISBAND = 0x2C;
	public const PARTY_INFO_DISBAND = 0x2D;
	public const PARTY_REQUEST_QUIT = 0x2E;
	public const PARTY_REQUEST_PROMOTE = 0x2F;
	public const PARTY_INFO_PROMOTE = 0x30;
	public const PARTY_REQUEST_KICK = 0x31;
	public const PARTY_REQUEST_WARP = 0x32;
	public const PARTY_INFO_WARP = 0x33;
	public const PARTY_REQUEST_CHAT = 0x34;
	public const PARTY_INFO_CHAT = 0x35;
	public const PARTY_REQUEST_FORCE = 0x36;
	public const PARTY_INFO_OFFLINE = 0x37;
}