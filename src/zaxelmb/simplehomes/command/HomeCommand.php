<?php

namespace zaxelmb\simplehomes\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;
use zaxelmb\simplehomes\Loader;
use zaxelmb\simplehomes\task\TeleportTask;

class HomeCommand extends Command implements PluginOwned {
    
    private Loader $plugin;
    
    /** @var array<string, int> */
    private static array $cooldowns = [];
    
    public function __construct() {
        parent::__construct("home", "Teletransportar a tu home", "/home <nombre>");
        $this->setPermission("simplehomes.command.home");
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
            $homes = $this->plugin->getHomeManager()->getHomeNames($sender);
            
            if (empty($homes)) {
                $sender->sendMessage($this->getMessage("no-homes"));
            } else {
                $homesList = implode("§7, §a", $homes);
                $sender->sendMessage($this->getMessage("home-list", ["{homes}" => $homesList]));
            }
            $sender->sendMessage($this->getMessage("usage-home"));
            return false;
        }
        
        $homeName = strtolower($args[0]);
        
        if (!$this->checkCooldown($sender)) {
            $remaining = $this->getRemainingCooldown($sender);
            $sender->sendMessage($this->getMessage("cooldown", ["{time}" => $remaining]));
            return false;
        }
        
        if (!$this->plugin->getHomeManager()->hasHome($sender, $homeName)) {
            $sender->sendMessage($this->getMessage("home-not-found", ["{home}" => $homeName]));
            return false;
        }
        
        $home = $this->plugin->getHomeManager()->getHome($sender, $homeName);
        
        if ($home === null) {
            $sender->sendMessage($this->getMessage("home-not-found", ["{home}" => $homeName]));
            return false;
        }
        
        $position = $home->getPosition();
        
        if ($position === null) {
            $sender->sendMessage("§cEl mundo de tu home no está cargado");
            return false;
        }
        
        $delay = $this->plugin->getConfig()->get("teleport-delay", 3);
        
        if ($delay > 0) {
            $sender->sendMessage($this->getMessage("teleporting", ["{delay}" => $delay]));
            
            $task = new TeleportTask($sender, $position, $home->getYaw(), $home->getPitch(), $homeName);
            $this->plugin->getScheduler()->scheduleDelayedTask($task, $delay * 20);
        } else {
            $sender->teleport($position);
            $sender->sendMessage($this->getMessage("teleported", ["{home}" => $homeName]));
            $this->setCooldown($sender);
        }
        
        return true;
    }
    
    private function checkCooldown(Player $player): bool {
        if ($player->hasPermission("simplehomes.bypass.cooldown")) {
            return true;
        }
        
        $name = strtolower($player->getName());
        
        if (!isset(self::$cooldowns[$name])) {
            return true;
        }
        
        return time() >= self::$cooldowns[$name];
    }
    
    private function getRemainingCooldown(Player $player): int {
        $name = strtolower($player->getName());
        
        if (!isset(self::$cooldowns[$name])) {
            return 0;
        }
        
        $remaining = self::$cooldowns[$name] - time();
        return max(0, $remaining);
    }
    
    public static function setCooldown(Player $player): void {
        if ($player->hasPermission("simplehomes.bypass.cooldown")) {
            return;
        }
        
        $cooldown = Loader::getInstance()->getConfig()->get("teleport-cooldown", 5);
        $name = strtolower($player->getName());
        self::$cooldowns[$name] = time() + $cooldown;
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