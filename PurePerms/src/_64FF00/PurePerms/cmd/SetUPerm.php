<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use function count;

class SetUPerm extends Command {

    private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
		parent::__construct(
			"setuperm",
			TF::AQUA . "Add a permission to a specific player!",
			TF::RED . "Usage: " . TF::GRAY . "/setuperm <player> <permission>"
		);

		$this->plugin = $plugin;
		$this->setPermission("pperms.command.setuperm");
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(!$sender->hasPermission("pperms.command.setuperm")) {
			$sender->sendMessage(PurePerms::NO_PERMS);
			return;
		}
        
        if(count($args) < 2) {
            $sender->sendMessage($this->usageMessage);
            return;
        }
        
        $player = $this->plugin->getPlayer($args[0]);
        
        $this->plugin->getUserDataMgr()->setPermission($player, $args[1]);
        $sender->sendMessage(TF::GREEN . "Successfully added the permission $args[1] to the player {$player->getName()}!");
    }

}