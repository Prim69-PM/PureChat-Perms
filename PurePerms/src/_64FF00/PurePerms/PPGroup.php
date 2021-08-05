<?php

namespace _64FF00\PurePerms;
use function array_diff;
use function array_unique;
use function sort;
use function in_array;
use function array_merge;
use function is_array;
use function is_null;

class PPGroup {

	private $name;
	private PurePerms $plugin;

    private array $parents = [];

    public function __construct(PurePerms $plugin, $name){
        $this->plugin = $plugin;
        $this->name = $name;
    }

    public function __toString() : string {
        return $this->name;
    }

    public function getAlias(){
        return $this->getNode("alias") ?? $this->name;
    }

    public function getData(){
        return $this->plugin->getProvider()->getGroupData($this);
    }

    public function getGroupPermissions() : array {
        $permissions = $this->getNode("permissions");

        if(!is_array($permissions)) {
            $this->plugin->getLogger()->critical("Invalid 'permissions' node given to " .  __METHOD__);
            return [];
        }

        foreach($this->getParentGroups() as $parentGroup) {
            $parentPermissions = $parentGroup->getGroupPermissions();
            if(is_null($parentPermissions)) $parentPermissions = [];
            $permissions = array_merge($parentPermissions, $permissions);
        }

        return $permissions;
    }

    public function getName(){
        return $this->name;
    }

    public function getNode($node){
        return $this->getData()[$node] ?? null;
    }

    /**
     * @return PPGroup[]
     */
    public function getParentGroups() : array {
        if($this->parents === []) {
            if(!is_array($this->getNode('inheritance'))) {
                $this->plugin->getLogger()->critical("Invalid 'inheritance' node given to " . __METHOD__);
                return [];
            }

            foreach($this->getNode('inheritance') as $parentGroupName) {
                $parentGroup = $this->plugin->getGroup($parentGroupName);
                if($parentGroup !== null) $this->parents[] = $parentGroup;
            }
        }

        return $this->parents;
    }

    public function isDefault() : bool {
		return $this->getNode('isDefault') === true;
    }

    public function removeNode(string $node) : void {
        $tempGroupData = $this->getData();
        if(isset($tempGroupData[$node])) {
            unset($tempGroupData[$node]);
            $this->setData($tempGroupData);
        }
    }

    public function setData(array $data) : void {
        $this->plugin->getProvider()->setGroupData($this, $data);
    }

    public function setDefault() : void {
		$this->setNode("isDefault", true);
    }

    public function setGroupPermission(string $permission) : bool {
		$tempGroupData = $this->getData();
		$tempGroupData["permissions"][] = $permission;

		$this->setData($tempGroupData);
        $this->plugin->updatePlayersInGroup($this);
        return true;
    }

    public function setNode(string $node, string $value) : void {
        $tempGroupData = $this->getData();
        $tempGroupData[$node] = $value;
            
        $this->setData($tempGroupData);
    }

    public function sortPermissions() : void {
        $tempGroupData = $this->getData();
            
        if(isset($tempGroupData["permissions"])) {
            $tempGroupData["permissions"] = array_unique($tempGroupData["permissions"]);
            sort($tempGroupData["permissions"]);
        }
        
        $this->setData($tempGroupData);
    }

    public function unsetGroupPermission(string $permission) : bool {
		$tempGroupData = $this->getData();
		if(!in_array($permission, $tempGroupData["permissions"])) return false;
		$tempGroupData["permissions"] = array_diff($tempGroupData["permissions"], [$permission]);
		$this->setData($tempGroupData);
        $this->plugin->updatePlayersInGroup($this);
        return true;
    }

}