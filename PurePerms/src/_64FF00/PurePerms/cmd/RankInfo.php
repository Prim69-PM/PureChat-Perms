<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use function is_null;
use function implode;
use function count;

class RankInfo extends Command {

    private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
		parent::__construct(
			"rankinfo",
			TF::AQUA . "Shows information about a specific rank!",
			TF::RED . "Usage: " . TF::GRAY . "/rankinfo <rank>"
		);

		$this->plugin = $plugin;
		$this->setPermission("pperms.command.grpinfo");
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(!$sender->hasPermission("pperms.command.grpinfo")) {
			$sender->sendMessage(PurePerms::NO_PERMS);
			return;
		}
        
        if(count($args) < 1) {
            $sender->sendMessage($this->usageMessage);
            return;
        }

        $group = $this->plugin->getGroup($args[0]);
        if(is_null($group)) {
            $sender->sendMessage(TF::RED . "That rank does not exist!");
            return;
        }

        $parents = [];
        foreach($group->getParentGroups() as $tempGroup) $parents[] = $tempGroup->getName();

        $sender->sendMessage(
        	TF::GREEN . "-- Group Information for {$group->getName()} --\n" .
			"Alias: " . $group->getAlias() . "\n" .
			"Default Rank: " . $group->isDefault() ? "True" : "False" . "\n" .
			"Parents: " . (empty($parents) ? "None" : implode(", ", $parents))
		);
    }

}