<?php

namespace _64FF00\PurePerms;

use _64FF00\PurePerms\cmd\{AddRank,
	DefRank,
	RankInfo,
	Ranks,
	ListGPerms,
	ListUPerms,
	DelRank,
	SetGPerm,
	SetRank,
	SetUPerm,
	UnsetGPerm,
	UnsetUPerm
};
use _64FF00\PurePerms\data\UserDataManager;
use _64FF00\PurePerms\provider\ProviderInterface;
use _64FF00\PurePerms\provider\YamlV1Provider;
use pocketmine\IPlayer;
use pocketmine\permission\PermissionAttachment;
use pocketmine\permission\PermissionManager;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\UUID;
use RuntimeException;
use function count;
use function array_merge;
use function preg_match;
use function array_keys;
use function substr;

class PurePerms extends PluginBase {

    const NO_PERMS = TextFormat::DARK_RED . "You do not have permission to use this command!";
    const CORE_PERM = "\x70\x70\x65\x72\x6d\x73\x2e\x63\x6f\x6d\x6d\x61\x6e\x64\x2e\x70\x70\x69\x6e\x66\x6f";

    const NOT_FOUND = null;
    const INVALID_NAME = -1;
    const ALREADY_EXISTS = 0;
    const SUCCESS = 1;

    private bool $isGroupsLoaded = false;
    public static PurePerms $instance;

    private ProviderInterface $provider;
    private UserDataManager $userDataMgr;

    private array $attachments = [], $groups = [];

    public function onLoad(){
        $this->userDataMgr = new UserDataManager($this);
    }
    
    public function onEnable(){
        $this->registerCommands();
        $this->setProvider();
        $this->getServer()->getPluginManager()->registerEvents(new PPListener($this), $this);
		self::$instance = $this;
    }

    public function onDisable() {
		foreach($this->getServer()->getOnlinePlayers() as $player) {
			$this->unregisterPlayer($player);
		}
    }

    private function registerCommands(){
        $this->getServer()->getCommandMap()->registerAll($this->getName(), [
        	new AddRank($this),
			new DefRank($this),
			new Ranks($this),
			new RankInfo($this),
			new ListGPerms($this),
			new ListUPerms($this),
			new DelRank($this),
			new SetGPerm($this),
			new SetRank($this),
			new SetUPerm($this),
			new UnsetGPerm($this),
			new UnsetUPerm($this)
		]);
    }
    
    private function setProvider(){
        $provider = new YamlV1Provider($this);
        $this->provider = $provider;
        $this->updateGroups();
    }

    public function addGroup(string $groupName) : int{
        $groupsData = $this->getProvider()->getGroupsData();

        if(!$this->isValidGroupName($groupName)) return self::INVALID_NAME;
        if(isset($groupsData[$groupName])) return self::ALREADY_EXISTS;

        $groupsData[$groupName] = [
            "isDefault" => false,
            "inheritance" => [],
            "permissions" => []
        ];

        $this->getProvider()->setGroupsData($groupsData);
        $this->updateGroups();
        return self::SUCCESS;
    }

    public function getAttachment(Player $player) : ?PermissionAttachment {
        $uniqueId = $this->getValidUUID($player);

        if(!isset($this->attachments[$uniqueId])) throw new RuntimeException("Tried to calculate permissions on {$player->getName()} using null attachment");
        return $this->attachments[$uniqueId];
    }

    public function getDefaultGroup() : ?PPGroup {
        $defaultGroups = [];

        foreach($this->getGroups() as $defaultGroup) {
            if($defaultGroup->isDefault()) $defaultGroups[] = $defaultGroup;
        }

        if(count($defaultGroups) === 1) return $defaultGroups[0];

		if(count($defaultGroups) > 1) {
			$this->getLogger()->warning("More than one default group was declared in the groups file.");
		} elseif(count($defaultGroups) <= 0) {
			$this->getLogger()->warning("No default group was found in the groups file.");
		}

		$this->getLogger()->info("Setting the default group automatically.");

		foreach($this->getGroups() as $tempGroup) {
			if(count($tempGroup->getParentGroups()) === 0) {
				$this->setDefaultGroup($tempGroup);
				return $tempGroup;
			}
		}

        return null;
    }

    public function getGroup(string $groupName) : ?PPGroup {
        if(!isset($this->groups[$groupName])) {
            /** @var PPGroup $group */
            foreach($this->groups as $group) {
                if($group->getAlias() === $groupName) return $group;
            }

            $this->getLogger()->debug("Group $groupName was not found.");

            return null;
        }

        /** @var PPGroup $group */
        $group = $this->groups[$groupName];
        if(empty($group->getData())) {
            $this->getLogger()->warning("Group $groupName has invalid or corrupted data.");
            return null;
        }

        return $group;
    }

    /**
     * @return PPGroup[]
     */
    public function getGroups() : array {
        if(!$this->isGroupsLoaded) throw new RuntimeException("No groups loaded, maybe a provider error?");
        return $this->groups;
    }

    public function getPermissions(IPlayer $player) : array {
        $group = $this->userDataMgr->getGroup($player);
        $groupPerms = $group->getGroupPermissions();
        $userPerms = $this->userDataMgr->getUserPermissions($player);
        return array_merge($groupPerms, $userPerms);
    }

    public function getPlayer(string $name) {
        $player = $this->getServer()->getPlayer($name);
        return $player instanceof Player ? $player : $this->getServer()->getOfflinePlayer($name);
    }

    public function getProvider() : ProviderInterface {
        return $this->provider;
    }

    public function getUserDataMgr() : UserDataManager {
        return $this->userDataMgr;
    }

    public function getValidUUID(Player $player) : ?string {
        $uuid = $player->getUniqueId();
        if($uuid instanceof UUID) return $uuid->toString();
        $this->getLogger()->debug("Invalid UUID detected! (userName: " . $player->getName() . ", isConnected: " . ($player->isConnected() ? "true" : "false") . ", isOnline: " . ($player->isOnline() ? "true" : "false") . ", isValid: " . ($player->isValid() ? "true" : "false") .  ")");
        return null;
    }

    public function isValidGroupName(string $groupName) : int {
        return preg_match('/[0-9a-zA-Z\xA1-\xFE]$/', $groupName);
    }

    public function registerPlayer(Player $player) : void {
        $this->getLogger()->debug("Registering player {$player->getName()}...");
        $uniqueId = $this->getValidUUID($player);

        if(!isset($this->attachments[$uniqueId])) {
            $attachment = $player->addAttachment($this);
            $this->attachments[$uniqueId] = $attachment;
            $this->updatePermissions($player);
        }
    }

    public function removeGroup(string $groupName) : bool {
        if(!$this->isValidGroupName($groupName)) return self::INVALID_NAME;

        $groupsData = $this->getProvider()->getGroupsData();
        if(!isset($groupsData[$groupName])) return self::NOT_FOUND;

        unset($groupsData[$groupName]);
        $this->getProvider()->setGroupsData($groupsData);
        $this->updateGroups();
        return self::SUCCESS;
    }

    public function setDefaultGroup(PPGroup $group){
        foreach($this->getGroups() as $currentGroup) {
			$isDefault = $currentGroup->getNode("isDefault");
			if($isDefault) $currentGroup->removeNode("isDefault");
        }

        $group->setDefault();
    }

    public function sortGroupData(){
        foreach($this->getGroups() as $ppGroup) {
            $ppGroup->sortPermissions();
        }
    }

    public function updateGroups(){
        $this->groups = [];

        foreach(array_keys($this->getProvider()->getGroupsData()) as $groupName) {
            $this->groups[$groupName] = new PPGroup($this, $groupName);
        }

        if(empty($this->groups)) throw new RuntimeException("No groups found, I guess there's definitely something wrong with your data provider... *cough cough*");
        $this->isGroupsLoaded = true;
        $this->sortGroupData();
    }

    public function updatePermissions(IPlayer $player){
    	if(!$player instanceof Player) return;
		$permissions = [];
		foreach($this->getPermissions($player) as $permission) {
			if($permission === '*') {
				foreach(PermissionManager::getInstance()->getPermissions() as $tmp) {
					$permissions[$tmp->getName()] = true;
				}
			} else {
				$isNegative = substr($permission, 0, 1) === "-";
				if($isNegative) $permission = substr($permission, 1);
				$permissions[$permission] = !$isNegative;
			}
		}

		$permissions[self::CORE_PERM] = true;

		/** @var PermissionAttachment $attachment */
		$attachment = $this->getAttachment($player);
		$attachment->clearPermissions();
		$attachment->setPermissions($permissions);
    }

    public function updatePlayersInGroup(PPGroup $group){
        foreach($this->getServer()->getOnlinePlayers() as $player) {
            if($this->userDataMgr->getGroup($player) === $group) $this->updatePermissions($player);
        }
    }

    public function unregisterPlayer(Player $player){
        $this->getLogger()->debug("Unregistering player {$player->getName()}...");
        $uniqueId = $this->getValidUUID($player);

        if($uniqueId !== null) {
            if(isset($this->attachments[$uniqueId])) $player->removeAttachment($this->attachments[$uniqueId]);
            unset($this->attachments[$uniqueId]);
        }
    }

    public static function getInstance() : PurePerms {
    	return self::$instance;
	}

}
