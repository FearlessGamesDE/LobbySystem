<?php

namespace LobbySystem\packets;

class PacketPool
{
	/**
	 * Server 0x110 - 0x11F
	 */

	public const SERVER_ENABLE = 0x110;
	public const SERVER_DISABLE = 0x111;
	public const SERVER_DATA = 0x113; //TODO
	public const SERVER_PLAY = 0x114;
	public const SERVER_TEAM = 0x115;

	/**
	 * Party 0x120 - 0x14F
	 */

	public const PARTY_REQUEST_INVITE = 0x120;
	public const PARTY_INFO_EXPIRE = 0x121;
	public const PARTY_INFO_INVITE = 0x122;
	public const PARTY_INFO_NOT_IN_PARTY = 0x124;
	public const PARTY_INFO_IN_PARTY = 0x125;
	public const PARTY_INFO_JOIN = 0x126;
	public const PARTY_INFO_QUIT = 0x127;
	public const PARTY_REQUEST_LIST = 0x128;
	public const PARTY_INFO_LIST = 0x129;
	public const PARTY_INFO_NOPERMISSION_MODERATOR = 0x12A;
	public const PARTY_INFO_NOPERMISSION_OWNER = 0x12B;
	public const PARTY_REQUEST_DISBAND = 0x12C;
	public const PARTY_INFO_DISBAND = 0x12D;
	public const PARTY_REQUEST_QUIT = 0x12E;
	public const PARTY_REQUEST_PROMOTE = 0x12F;
	public const PARTY_INFO_PROMOTE = 0x130;
	public const PARTY_REQUEST_KICK = 0x131;
	public const PARTY_REQUEST_WARP = 0x132;
	public const PARTY_INFO_WARP = 0x133;
	public const PARTY_REQUEST_CHAT = 0x134;
	public const PARTY_INFO_CHAT = 0x135;
}