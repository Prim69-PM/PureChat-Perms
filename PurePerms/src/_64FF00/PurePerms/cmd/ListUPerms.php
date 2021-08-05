<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat as TF;

class ListUPerms extends Command {

    private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
		parent::__construct(
			"listuperms",
			TF::AQUA . "Displays a list of a players permissions!",
			TF::RED . "Usage: " . TF::GRAY . "/listuperms <player>"
		);

		$this->plugin = $plugin;
		$this->setPermission("pperms.command.listuperms");
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(!$sender->hasPermission("pperms.command.listuperms")) {
			$sender->sendMessage(PurePerms::NO_PERMS);
			return;
		}
        
        if(count($args) < 1) {
            $sender->sendMessage($this->usageMessage);
            return;
        }
        
        $player = $this->plugin->getPlayer($args[0]);
        
        $permissions = $this->plugin->getUserDataMgr()->getUserPermissions($player);
        
        if(empty($permissions)) {
            $sender->sendMessage(TF::GREEN . $player->getName() . " does not have any permissions!");
            return;
        }

        $pageHeight = $sender instanceof ConsoleCommandSender ? 24 : 6;
        $chunkedPermissions = array_chunk($permissions, $pageHeight);
        $maxPageNumber = count($chunkedPermissions);

        if(!isset($args[1]) || !is_numeric($args[1]) || $args[1] <= 0) {
            $pageNumber = 1;
        } elseif($args[1] > $maxPageNumber) {
            $pageNumber = $maxPageNumber;
        } else {
            $pageNumber = $args[1];
        }

		$perms = implode("\n" . TF::GREEN . " - ", $chunkedPermissions[$pageNumber - 1]);
		$sender->sendMessage(TF::GREEN . "List of all permissions for the player {$player->getName()} ($pageNumber / $maxPageNumber) :\n" . TF::GREEN . " - $perms");

    }

}