<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use function count;

class AddRank extends Command {

    private PurePerms $plugin;

    public function __construct(PurePerms $plugin){
		parent::__construct(
			"addrank",
			TF::AQUA . "Create a new rank!",
			TF::RED . "Usage: " . TF::GRAY . "/addrank <rank>"
		);

		$this->plugin = $plugin;
        $this->setPermission("pperms.command.addgroup");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
    	if(!$sender->hasPermission("pperms.command.addgroup")) {
    		$sender->sendMessage(PurePerms::NO_PERMS);
    		return;
		}
        
        if(count($args) < 1) {
            $sender->sendMessage($this->usageMessage);
            return;
        }

        $result = $this->plugin->addGroup($args[0]);
        $msg = $result === PurePerms::SUCCESS ? TF::GREEN . "Successfully created the rank: " . TF::WHITE . "$args[0]!" : ($result === PurePerms::ALREADY_EXISTS ? TF::RED . "That rank already exists!" : TF::RED . "That name is invalid!");
        $sender->sendMessage($msg);
    }

}