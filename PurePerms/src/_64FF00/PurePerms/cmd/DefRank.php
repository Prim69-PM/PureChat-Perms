<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use function is_null;
use function count;

class DefRank extends Command {

	private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
		parent::__construct(
			"defrank",
			TF::AQUA . "Set the default rank!",
			TF::RED . "Usage: " . TF::GRAY . "/defrank <rank>"
		);

		$this->plugin = $plugin;
		$this->setPermission("pperms.command.defgroup");
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender->hasPermission("pperms.command.defgroup") || $sender->getName() !== "xPrim69x") {
			$sender->sendMessage(PurePerms::NO_PERMS);
			return;
		}

        if(count($args) < 1) {
            $sender->sendMessage($this->usageMessage);
            return;
        }

        $rank = $this->plugin->getGroup($args[0]);
        if(is_null($rank)) {
            $sender->sendMessage(TF::RED . "That rank does not exist!");
            return;
        }

        $this->plugin->setDefaultGroup($rank);
        $sender->sendMessage(TF::GREEN . "The default rank has been set to " . TF::WHITE . "$args[0]!");
    }

}