<?php

namespace _64FF00\PurePerms\provider;

use _64FF00\PurePerms\PPGroup;
use pocketmine\IPlayer;

interface ProviderInterface{

    public function getGroupData(PPGroup $group);

    public function getGroupsData();

    public function getPlayerData(IPlayer $player);

    public function getUsers();

    public function setGroupData(PPGroup $group, array $tempGroupData);

    public function setGroupsData(array $tempGroupsData);

    public function setPlayerData(IPlayer $player, array $tempPlayerData);
}