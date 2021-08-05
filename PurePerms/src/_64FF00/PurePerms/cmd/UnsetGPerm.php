<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use function is_null;
use function count;

class UnsetGPerm extends Command {

    private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
		parent::__construct(
			"unsetgperm",
			TF::AQUA . "Remove a permission from a specific rank!",
			TF::RED . "Usage: " . TF::GRAY . "/unsetgperm <rank> <permission>"
		);

		$this->plugin = $plugin;
		$this->setPermission("pperms.command.unsetgperm");
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(!$sender->hasPermission("pperms.command.unsetgperm")) {
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

        $group->unsetGroupPermission($args[1]);
        $sender->sendMessage(TF::GREEN . "Successfully removed the permission $args[1] from the rank {$group->getName()}!");
    }

}