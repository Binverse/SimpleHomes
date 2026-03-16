<?php

namespace zaxelmb\simplehomes\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;
use zaxelmb\simplehomes\Loader;

class HomesCommand extends Command implements PluginOwned {
    
    private Loader $plugin;
    
    public function __construct() {
        parent::__construct("homes", "Ver todos tus homes", "/homes", ["listhomes", "homelist"]);
        $this->setPermission("simplehomes.command.homes");
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
        
        $homeManager = $this->plugin->getHomeManager();
        $homes = $homeManager->getHomes($sender);
        
        if (empty($homes)) {
            $sender->sendMessage($this->getMessage("no-homes"));
            return false;
        }
        
        $homeCount = count($homes);
        $homeLimit = $homeManager->getHomeLimit($sender);
        
        $sender->sendMessage("§8§l§m                                    ");
        $sender->sendMessage("§a§lMis Homes §7({$homeCount}/{$homeLimit})");
        $sender->sendMessage("§8§m                                    ");
        
        foreach ($homes as $home) {
            $name = $home->getName();
            $world = $home->getWorldName();
            $x = round($home->getX());
            $y = round($home->getY());
            $z = round($home->getZ());
            
            $sender->sendMessage("§e▪ §l{$name} §r§7- {$world} §8(§7X:{$x} Y:{$y} Z:{$z}§8)");
        }
        
        $sender->sendMessage("§8§m                                    ");
        $sender->sendMessage("§7Usa §e/home <nombre> §7para teletransportarte");
        
        return true;
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