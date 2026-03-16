<?php

namespace zaxelmb\simplehomes\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\Position;
use zaxelmb\simplehomes\Loader;
use zaxelmb\simplehomes\command\HomeCommand;

class TeleportTask extends Task {
    
    private Player $player;
    private Position $position;
    private float $yaw;
    private float $pitch;
    private string $homeName;
    private Position $startPosition;
    private float $startHealth;
    
    public function __construct(Player $player, Position $position, float $yaw, float $pitch, string $homeName) {
        $this->player = $player;
        $this->position = $position;
        $this->yaw = $yaw;
        $this->pitch = $pitch;
        $this->homeName = $homeName;
        $this->startPosition = $player->getPosition();
        $this->startHealth = $player->getHealth();
    }
    
    public function onRun(): void {
        if (!$this->player->isOnline()) {
            return;
        }
        
        $config = Loader::getInstance()->getConfig();
        
        if ($config->get("cancel-on-move", true)) {
            if (!$this->isSamePosition($this->player->getPosition(), $this->startPosition)) {
                $this->player->sendMessage($this->getMessage("teleport-cancelled-move"));
                return;
            }
        }
        
        if ($config->get("cancel-on-damage", true)) {
            if ($this->player->getHealth() < $this->startHealth) {
                $this->player->sendMessage($this->getMessage("teleport-cancelled-damage"));
                return;
            }
        }
        
        $location = $this->player->getLocation();
        $location->x = $this->position->x;
        $location->y = $this->position->y;
        $location->z = $this->position->z;
        $location->world = $this->position->world;
        $location->yaw = $this->yaw;
        $location->pitch = $this->pitch;
        
        $this->player->teleport($location);
        $this->player->sendMessage($this->getMessage("teleported", ["{home}" => $this->homeName]));
        
        HomeCommand::setCooldown($this->player);
    }
    
    private function isSamePosition(Position $pos1, Position $pos2): bool {
        $threshold = 0.5;
        
        return abs($pos1->x - $pos2->x) < $threshold &&
               abs($pos1->y - $pos2->y) < $threshold &&
               abs($pos1->z - $pos2->z) < $threshold &&
               $pos1->world->getFolderName() === $pos2->world->getFolderName();
    }
    
    private function getMessage(string $key, array $replacements = []): string {
        $config = Loader::getInstance()->getConfig();
        $prefix = $config->getNested("messages.prefix", "§8[§aHomes§8]§r");
        $message = $config->getNested("messages." . $key, $key);
        
        foreach ($replacements as $search => $replace) {
            $message = str_replace($search, $replace, $message);
        }
        
        return $prefix . " " . $message;
    }
}