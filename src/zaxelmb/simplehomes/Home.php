<?php

namespace zaxelmb\simplehomes;

use pocketmine\world\Position;
use pocketmine\Server;

class Home {
    
    private string $name;
    private string $worldName;
    private float $x;
    private float $y;
    private float $z;
    private float $yaw;
    private float $pitch;
    
    public function __construct(string $name, Position $position, float $yaw = 0.0, float $pitch = 0.0) {
        $this->name = $name;
        $this->worldName = $position->getWorld()->getFolderName();
        $this->x = $position->getX();
        $this->y = $position->getY();
        $this->z = $position->getZ();
        $this->yaw = $yaw;
        $this->pitch = $pitch;
    }
    
    public function getName(): string {
        return $this->name;
    }
    
    public function getWorldName(): string {
        return $this->worldName;
    }
    
    public function getX(): float {
        return $this->x;
    }
    
    public function getY(): float {
        return $this->y;
    }
    
    public function getZ(): float {
        return $this->z;
    }
    
    public function getYaw(): float {
        return $this->yaw;
    }
    
    public function getPitch(): float {
        return $this->pitch;
    }
    
    /**
     * @return Position|null
     */
    public function getPosition(): ?Position {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($this->worldName);
        if ($world === null) {
            return null;
        }
        return new Position($this->x, $this->y, $this->z, $world);
    }
    
    /**
     * @return array
     */
    public function toArray(): array {
        return [
            "name" => $this->name,
            "world" => $this->worldName,
            "x" => $this->x,
            "y" => $this->y,
            "z" => $this->z,
            "yaw" => $this->yaw,
            "pitch" => $this->pitch
        ];
    }
    
    /**
     * @param array $data
     * @return Home|null
     */
    public static function fromArray(array $data): ?Home {
        if (!isset($data["name"], $data["world"], $data["x"], $data["y"], $data["z"])) {
            return null;
        }
        
        $world = Server::getInstance()->getWorldManager()->getWorldByName($data["world"]);
        if ($world === null) {
            return null;
        }
        
        $position = new Position($data["x"], $data["y"], $data["z"], $world);
        $yaw = $data["yaw"] ?? 0.0;
        $pitch = $data["pitch"] ?? 0.0;
        
        return new Home($data["name"], $position, $yaw, $pitch);
    }
}