<?php

namespace _64FF00\PurePerms\data;

use _64FF00\PurePerms\PPGroup;
use _64FF00\PurePerms\PurePerms;
use _64FF00\PurePerms\event\PPGroupChangedEvent;
use pocketmine\IPlayer;
use function is_null;
use function is_array;
use function in_array;
use function array_diff;

class UserDataManager {

	private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
        $this->plugin = $plugin;
    }

    public function getData(IPlayer $player) : array {
        return $this->plugin->getProvider()->getPlayerData($player);
    }

    public function getGroup(IPlayer $player) : ?PPGroup {
        $groupName = $this->getNode($player, "group");

        $group = $this->plugin->getGroup($groupName);

        if(is_null($group)) {
            $this->plugin->getLogger()->critical("Invalid group name found in " . $player->getName() . "'s player data");
            $this->plugin->getLogger()->critical("Restoring the group data to 'default'");

            $defaultGroup = $this->plugin->getDefaultGroup();
            $this->setGroup($player, $defaultGroup);

            return $defaultGroup;
        }

        return $group;
    }

    public function getNode(IPlayer $player, $node){
        $userData = $this->getData($player);
        if(!isset($userData[$node])) return null;
        return $userData[$node];
    }

    public function getUserPermissions(IPlayer $player) : array {
        $permissions = $this->getNode($player, "permissions");

        if(!is_array($permissions)) {
            $this->plugin->getLogger()->critical("Invalid 'permissions' node given to " . __METHOD__ . '()');
            return [];
        }

        return $permissions;
    }

    public function setData(IPlayer $player, array $data) : void {
        $this->plugin->getProvider()->setPlayerData($player, $data);
    }

    public function setGroup(IPlayer $player, PPGroup $group) : void {
		$this->setNode($player, "group", $group->getName());

        $event = new PPGroupChangedEvent($this->plugin, $player, $group);
        $event->call();
    }

    public function setNode(IPlayer $player, $node, $value) : void {
        $tempUserData = $this->getData($player);
        $tempUserData[$node] = $value;

        $this->setData($player, $tempUserData);
    }

    public function setPermission(IPlayer $player, $permission) : void {
		$tempUserData = $this->getData($player);
		$tempUserData["permissions"][] = $permission;

		$this->setData($player, $tempUserData);
        $this->plugin->updatePermissions($player);
    }

    public function unsetPermission(IPlayer $player, $permission) : void {
		$tempUserData = $this->getData($player);

		if(!in_array($permission, $tempUserData["permissions"])) return;
		$tempUserData["permissions"] = array_diff($tempUserData["permissions"], [$permission]);

		$this->setData($player, $tempUserData);
        $this->plugin->updatePermissions($player);
    }

}