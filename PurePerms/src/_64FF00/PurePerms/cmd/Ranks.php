<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use function implode;

class Ranks extends Command {

    private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
		parent::__construct(
			"ranks",
			TF::AQUA . "Lists all the ranks!",
		);

		$this->plugin = $plugin;
		$this->setPermission("pperms.command.groups");
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(!$sender->hasPermission("pperms.command.groups")) {
			$sender->sendMessage(PurePerms::NO_PERMS);
			return;
		}

        $result = [];
        foreach($this->plugin->getGroups() as $group) $result[] = $group->getName();

        $sender->sendMessage(TF::GREEN . "Ranks: " . implode(", ", $result));
    }

}