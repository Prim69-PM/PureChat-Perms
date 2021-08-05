<?php

namespace _64FF00\PurePerms;

use _64FF00\PurePerms\event\PPGroupChangedEvent;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\Player;

class PPListener implements Listener {

    private PurePerms $plugin;

    public function __construct(PurePerms $plugin){
        $this->plugin = $plugin;
    }

    public function onGroupChanged(PPGroupChangedEvent $event){
        $this->plugin->updatePermissions($event->getPlayer());
    }

    /**
     * @param EntityLevelChangeEvent $event
     * @priority MONITOR
     */
    public function onLevelChange(EntityLevelChangeEvent $event){
        if($event->isCancelled()) return;

        $player = $event->getEntity();
        if($player instanceof Player) {
            $this->plugin->updatePermissions($player);
        }
    }

    /**
     * @param PlayerLoginEvent $event
     * @priority LOWEST
     */
    public function onLogin(PlayerLoginEvent $event){
        $this->plugin->registerPlayer($event->getPlayer());
    }

    /**
     * @param PlayerQuitEvent $event
     * @priority HIGHEST
     */
    public function onQuit(PlayerQuitEvent $event){
        $this->plugin->unregisterPlayer($event->getPlayer());
    }

}