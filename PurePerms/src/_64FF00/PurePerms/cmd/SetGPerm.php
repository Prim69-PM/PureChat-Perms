<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;

class SetGPerm extends Command {

    private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
		parent::__construct(
			"setgperm",
			TF::AQUA . "Add a permission to a specific rank!",
			TF::RED . "Usage: " . TF::GRAY . "/setgperm <rank> <permission>"
		);

		$this->plugin = $plugin;
		$this->setPermission("pperms.command.setgperm");
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(!$sender->hasPermission("pperms.command.setgperm")) {
			$sender->sendMessage(PurePerms::NO_PERMS);
			return;
		}
        
        if(count($args) < 2) {
            $sender->sendMessage($this->usageMessage);
            return;
        }
        
        $group = $this->plugin->getGroup($args[0]);
        
        if(is_null($group)) {
            $sender->sendMessage(TF::RED . "That rank does not exist!");
            return;
        }
        
        $group->setGroupPermission($args[1]);
        $sender->sendMessage(TF::GREEN . "The permission $args[1] has been added to the rank {$group->getName()}!");
    }

}