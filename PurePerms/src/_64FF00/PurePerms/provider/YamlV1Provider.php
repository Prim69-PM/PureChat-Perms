<?php

namespace _64FF00\PurePerms\provider;

use _64FF00\PurePerms\PurePerms;
use _64FF00\PurePerms\PPGroup;
use pocketmine\IPlayer;
use pocketmine\utils\Config;
use RuntimeException;
use function get_class;
use function file_exists;
use function strtolower;
use function is_array;
use function mkdir;

class YamlV1Provider implements ProviderInterface{

	private Config $groups;
	private string $userDataFolder;
	private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
        $this->plugin = $plugin;

        $this->plugin->saveResource("groups.yml");
        $this->groups = new Config($this->plugin->getDataFolder() . "groups.yml", Config::YAML, []);
        $this->userDataFolder = $this->plugin->getDataFolder() . "players/";

        if(!file_exists($this->userDataFolder)) mkdir($this->userDataFolder, 0777, true);
    }

    public function getGroupData(PPGroup $group) : array {
        $groupName = $group->getName();
        if(!isset($this->getGroupsData()[$groupName]) || !is_array($this->getGroupsData()[$groupName])) return [];
        return $this->getGroupsData()[$groupName];
    }

    public function getGroupsData() : array {
        return $this->groups->getAll();
    }

    public function getPlayerConfig(IPlayer $player, $onUpdate = false){
        $userName = $player->getName();

        if($onUpdate) {
            if(!file_exists($this->userDataFolder . strtolower($userName) . ".yml")) {
                return new Config($this->userDataFolder . strtolower($userName) . ".yml", Config::YAML, [
                    "userName" => $userName,
                    "group" => $this->plugin->getDefaultGroup()->getName(),
                    "permissions" => []
                ]);
            }
            return new Config($this->userDataFolder . strtolower($userName) . ".yml", Config::YAML, []);
        } else {
            if(file_exists($this->userDataFolder . strtolower($userName) . ".yml")) {
				return new Config($this->userDataFolder . strtolower($userName) . ".yml", Config::YAML, []);
            }
			return [
				"userName" => $userName,
				"group" => $this->plugin->getDefaultGroup()->getName(),
				"permissions" => []
			];
        }
    }

    public function getPlayerData(IPlayer $player){
        $userConfig = $this->getPlayerConfig($player);

        return (($userConfig instanceof Config) ? $userConfig->getAll() : $userConfig);
    }

    public function getUsers(){
    }

    public function setGroupData(PPGroup $group, array $tempGroupData){
        $groupName = $group->getName();
        $this->groups->set($groupName, $tempGroupData);
        $this->groups->save();
    }

    public function setGroupsData(array $tempGroupsData){
        $this->groups->setAll($tempGroupsData);
        $this->groups->save();
    }

    public function setPlayerData(IPlayer $player, array $tempPlayerData){
        $userData = $this->getPlayerConfig($player, true);

        if(!$userData instanceof Config) throw new RuntimeException("Failed to update player data: Invalid data type (" . get_class($userData) . ")");

        $userData->setAll($tempPlayerData);
        $userData->save();
    }

}