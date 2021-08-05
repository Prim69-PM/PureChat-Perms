<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use function count;

class UnsetUPerm extends Command {

    private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
		parent::__construct(
			"unsetuperm",
			TF::AQUA . "Remove a permission from a specific player!",
			TF::RED . "Usage: " . TF::GRAY . "/unsetuperm <player> <permission>"
		);

		$this->plugin = $plugin;
		$this->setPermission("pperms.command.unsetuperm");
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(!$sender->hasPermission("pperms.command.unsetuperm")) {
			$sender->sendMessage(PurePerms::NO_PERMS);
			return;
		}
        
        if(count($args) < 2) {
            $sender->sendMessage($this->usageMessage);
            return;
        }
        
        $player = $this->plugin->getPlayer($args[0]);
        $permission = $args[1];
        $this->plugin->getUserDataMgr()->unsetPermission($player, $permission);
        
        $sender->sendMessage(TF::GREEN . "Succesfully removed permission $permission from {$player->getName()}!");
    }

}