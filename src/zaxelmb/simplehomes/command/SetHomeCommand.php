<?php

namespace zaxelmb\simplehomes\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;
use zaxelmb\simplehomes\Loader;

class SetHomeCommand extends Command implements PluginOwned {
    
    private Loader $plugin;
    
    public function __construct() {
        parent::__construct("sethome", "Establecer un home", "/sethome <nombre>", ["addhome"]);
        $this->setPermission("simplehomes.command.sethome");
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
            $sender->sendMessage($this->getMessage("usage-sethome"));
            return false;
        }
        
        $homeName = strtolower($args[0]);
        
        if (!$this->isValidHomeName($homeName)) {
            $sender->sendMessage("§cNombre de home inválido. Solo letras, números y guiones bajos");
            return false;
        }
        
        $homeManager = $this->plugin->getHomeManager();
        
        if ($homeManager->hasHome($sender, $homeName)) {
            $sender->sendMessage($this->getMessage("home-already-exists", ["{home}" => $homeName]));
            return false;
        }
        
        if (!$homeManager->canSetHome($sender)) {
            $limit = $homeManager->getHomeLimit($sender);
            $sender->sendMessage($this->getMessage("max-homes", ["{max}" => $limit]));
            return false;
        }
        
        if ($homeManager->setHome($sender, $homeName)) {
            $sender->sendMessage($this->getMessage("home-set", ["{home}" => $homeName]));
            return true;
        }
        
        $sender->sendMessage("§cError al establecer el home");
        return false;
    }
    
    private function isValidHomeName(string $name): bool {
        return preg_match('/^[a-z0-9_]+$/i', $name) === 1;
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