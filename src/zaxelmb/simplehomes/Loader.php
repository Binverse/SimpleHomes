<?php

namespace zaxelmb\simplehomes;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use zaxelmb\simplehomes\command\HomeCommand;
use zaxelmb\simplehomes\command\SetHomeCommand;
use zaxelmb\simplehomes\command\DelHomeCommand;
use zaxelmb\simplehomes\command\HomesCommand;

class Loader extends PluginBase implements Listener {
  
    private static Loader $instance;
    private HomeManager $homeManager;
  
    public function onEnable(): void {
        self::$instance = $this;
        
        if (!file_exists($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }
        
        $this->saveDefaultConfig();
        
        $this->getLogger()->info("SimpleHomes plugin enabled");
        
        $this->homeManager = new HomeManager($this);
        $this->registerCommands();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
  
    public function onDisable(): void {
        $this->homeManager->saveHomes();
        $this->getLogger()->info("SimpleHomes plugin disabled");
    }
  
    public static function getInstance(): Loader {
        return self::$instance;
    }
  
    public function getHomeManager(): HomeManager {
        return $this->homeManager;
    }
  
    public function registerCommands(): void {
        $this->getServer()->getCommandMap()->registerAll("SimpleHomes", [
            new HomeCommand(),
            new SetHomeCommand(),
            new DelHomeCommand(),
            new HomesCommand()
        ]);
    }
}