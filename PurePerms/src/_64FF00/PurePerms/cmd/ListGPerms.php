<?php

namespace _64FF00\PurePerms\cmd;

use _64FF00\PurePerms\PurePerms;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat as TF;
use function count;
use function implode;
use function array_chunk;
use function is_null;
use function is_numeric;

class ListGPerms extends Command {

    private PurePerms $plugin;

	public function __construct(PurePerms $plugin){
		parent::__construct(
			"listgperms",
			TF::AQUA . "Shows a list of a ranks permissions!",
			TF::RED . "Usage: " . TF::GRAY . "/listgperms <rank> [page]"
		);

		$this->plugin = $plugin;
		$this->setPermission("pperms.command.listgperms");
	}

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if(!$sender->hasPermission("pperms.command.listgperms")) {
			$sender->sendMessage(PurePerms::NO_PERMS);
			return;
		}

		if(count($args) < 1){
			$sender->sendMessage($this->usageMessage);
			return;
		}
        
        $group = $this->plugin->getGroup($args[0]);
        
        if(is_null($group)) {
            $sender->sendMessage(TF::RED . "That rank does not exist!");
            return;
        }
        
        $permissions = $group->getGroupPermissions();
        if(empty($permissions)) {
            $sender->sendMessage(TF::GREEN . "The rank {$group->getName()} does not have any permissions!");
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
        $sender->sendMessage(TF::GREEN . "List of all rank permissions for {$group->getName()} ($pageNumber / $maxPageNumber) :\n" . TF::GREEN . " - $perms");
    }

}