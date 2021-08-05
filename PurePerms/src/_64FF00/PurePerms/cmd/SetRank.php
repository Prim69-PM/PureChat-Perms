<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use function is_null;
use function count;

class SetRank extends Command {

    private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
		parent::__construct(
			"setrank",
			TF::AQUA . "Set a players rank!",
			TF::RED . "Usage: " . TF::GRAY . "/setrank <player> <rank>"
		);

		$this->plugin = $plugin;
		$this->setPermission("pperms.command.setgroup");
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(!$sender->hasPermission("pperms.command.setgroup")) {
			$sender->sendMessage(PurePerms::NO_PERMS);
			return;
		}
        
        if(count($args) < 2) {
            $sender->sendMessage($this->usageMessage);
            return;
        }
        
        $player = $this->plugin->getPlayer($args[0]);
        $group = $this->plugin->getGroup($args[1]);
        
        if(is_null($group)){
            $sender->sendMessage(TF::RED . "That rank does not exist!");
            return;
        }

        $this->plugin->getUserDataMgr()->setGroup($player, $group);
        $sender->sendMessage(TF::GREEN . "You have set {$player->getName()}'s rank to {$group->getName()}!");
        
        if($player instanceof Player) {
        	$player->sendMessage(TF::LIGHT_PURPLE . $sender->getName() . TF::GRAY . " has set your rank to " . TF::LIGHT_PURPLE . $group->getName() . "!");
        }
    }

}