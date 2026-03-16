<?php

namespace zaxelmb\simplehomes;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\world\Position;

class HomeManager {
    
    private Loader $plugin;
    private Config $homesConfig;
    
    /** @var array<string, array<string, Home>> */
    private array $homes = [];
    
    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        $this->loadHomes();
    }
    
    private function loadHomes(): void {
        $this->homesConfig = new Config($this->plugin->getDataFolder() . "homes.yml", Config::YAML);
        
        foreach ($this->homesConfig->getAll() as $playerName => $playerHomes) {
            if (!is_array($playerHomes)) continue;
            
            foreach ($playerHomes as $homeName => $homeData) {
                if (!is_array($homeData)) continue;
                
                $home = Home::fromArray($homeData);
                if ($home !== null) {
                    $this->homes[strtolower($playerName)][$homeName] = $home;
                }
            }
        }
    }
    
    public function saveHomes(): void {
        $data = [];
        
        foreach ($this->homes as $playerName => $playerHomes) {
            $data[$playerName] = [];
            foreach ($playerHomes as $homeName => $home) {
                $data[$playerName][$homeName] = $home->toArray();
            }
        }
        
        $this->homesConfig->setAll($data);
        $this->homesConfig->save();
    }
    
    /**
     * @param Player $player
     * @param string $homeName
     * @return bool
     */
    public function setHome(Player $player, string $homeName): bool {
        $playerName = strtolower($player->getName());
        $homeName = strtolower($homeName);
        
        if (!$this->canSetHome($player)) {
            return false;
        }
        
        $position = $player->getPosition();
        $home = new Home($homeName, $position, $player->getLocation()->getYaw(), $player->getLocation()->getPitch());
        
        if (!isset($this->homes[$playerName])) {
            $this->homes[$playerName] = [];
        }
        
        $this->homes[$playerName][$homeName] = $home;
        $this->saveHomes();
        
        return true;
    }
    
    /**
     * @param Player $player
     * @param string $homeName
     * @return bool
     */
    public function deleteHome(Player $player, string $homeName): bool {
        $playerName = strtolower($player->getName());
        $homeName = strtolower($homeName);
        
        if (!isset($this->homes[$playerName][$homeName])) {
            return false;
        }
        
        unset($this->homes[$playerName][$homeName]);
        
        if (empty($this->homes[$playerName])) {
            unset($this->homes[$playerName]);
        }
        
        $this->saveHomes();
        return true;
    }
    
    /**
     * @param Player $player
     * @param string $homeName
     * @return Home|null
     */
    public function getHome(Player $player, string $homeName): ?Home {
        $playerName = strtolower($player->getName());
        $homeName = strtolower($homeName);
        
        return $this->homes[$playerName][$homeName] ?? null;
    }
    
    /**
     * @param Player $player
     * @return array<string, Home>
     */
    public function getHomes(Player $player): array {
        $playerName = strtolower($player->getName());
        return $this->homes[$playerName] ?? [];
    }
    
    /**
     * @param Player $player
     * @return array<string>
     */
    public function getHomeNames(Player $player): array {
        return array_keys($this->getHomes($player));
    }
    
    /**
     * @param Player $player
     * @param string $homeName
     * @return bool
     */
    public function hasHome(Player $player, string $homeName): bool {
        $playerName = strtolower($player->getName());
        $homeName = strtolower($homeName);
        
        return isset($this->homes[$playerName][$homeName]);
    }
    
    /**
     * @param Player $player
     * @return int
     */
    public function getHomeCount(Player $player): int {
        $playerName = strtolower($player->getName());
        return count($this->homes[$playerName] ?? []);
    }
    
    /**
     * @param Player $player
     * @return int
     */
    public function getHomeLimit(Player $player): int {
        for ($i = 10; $i >= 1; $i--) {
            if ($player->hasPermission("simplehomes.limit." . $i)) {
                return $i;
            }
        }
        
        return $this->plugin->getConfig()->get("max-homes", 1);
    }
    
    /**
     * @param Player $player
     * @return bool
     */
    public function canSetHome(Player $player): bool {
        return $this->getHomeCount($player) < $this->getHomeLimit($player);
    }
    
    /**
     * @param Player $player
     */
    public function deleteAllHomes(Player $player): void {
        $playerName = strtolower($player->getName());
        
        if (isset($this->homes[$playerName])) {
            unset($this->homes[$playerName]);
            $this->saveHomes();
        }
    }
}