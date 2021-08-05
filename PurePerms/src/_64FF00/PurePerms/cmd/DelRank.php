<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;

class DelRank extends Command {

    private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
		parent::__construct(
			"delrank",
			TF::AQUA . "Delete a rank!",
			TF::RED . "Usage: " . TF::GRAY . "/delrank <rank>",
		);

		$this->plugin = $plugin;
		$this->setPermission("pperms.command.rmgroup");
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(!$sender->hasPermission("pperms.command.rmgroup")) {
			$sender->sendMessage(PurePerms::NO_PERMS);
			return;
		}
        
        if(count($args) < 1) {
            $sender->sendMessage($this->usageMessage);
            return;
        }

        $result = $this->plugin->removeGroup($args[0]);
        $msg = $result == PurePerms::SUCCESS ? TF::GREEN . "You have deleted the rank $args[0]!" : TF::RED . "That rank does not exist!";

        $sender->sendMessage($msg);
    }

}