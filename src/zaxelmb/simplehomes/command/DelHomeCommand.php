<?php

namespace zaxelmb\simplehomes\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;
use zaxelmb\simplehomes\Loader;

class DelHomeCommand extends Command implements PluginOwned {
    
    private Loader $plugin;
    
    public function __construct() {
        parent::__construct("delhome", "Eliminar un home", "/delhome <nombre>", ["removehome", "rmhome"]);
        $this->setPermission("simplehomes.command.delhome");
        $this->plugin = Loader::getInstance();
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cEste comando solo puede ser usado en juego");
            return false;
        }
        
        if (!$this->testPermission($sender)) {
            return false;
        }
        
        if (count($args) < 1) {
            $sender->sendMessage($this->getMessage("usage-delhome"));
            return false;
        }
        
        $homeName = strtolower($args[0]);
        
        if (!$this->plugin->getHomeManager()->hasHome($sender, $homeName)) {
            $sender->sendMessage($this->getMessage("home-not-found", ["{home}" => $homeName]));
            return false;
        }
        
        if ($this->plugin->getHomeManager()->deleteHome($sender, $homeName)) {
            $sender->sendMessage($this->getMessage("home-deleted", ["{home}" => $homeName]));
            return true;
        }
        
        $sender->sendMessage("§cError al eliminar el home");
        return false;
    }
    
    private function getMessage(string $key, array $replacements = []): string {
        $prefix = $this->plugin->getConfig()->getNested("messages.prefix", "§8[§aHomes§8]§r");
        $message = $this->plugin->getConfig()->getNested("messages." . $key, $key);
        
        foreach ($replacements as $search => $replace) {
            $message = str_replace($search, $replace, $message);
        }
        
        return $prefix . " " . $message;
    }
    
    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }
}