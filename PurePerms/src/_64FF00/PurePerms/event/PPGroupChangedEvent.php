<?php

namespace _64FF00\PurePerms\event;

use _64FF00\PurePerms\PPGroup;
use _64FF00\PurePerms\PurePerms;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\IPlayer;

class PPGroupChangedEvent extends PluginEvent {

	private PPGroup $group;
	private IPlayer $player;

	public function __construct(PurePerms $plugin, IPlayer $player, PPGroup $group){
        parent::__construct($plugin);
        $this->group = $group;
        $this->player = $player;
    }

    public function getGroup() : PPGroup {
        return $this->group;
    }

    public function getPlayer() : IPlayer {
        return $this->player;
    }

}