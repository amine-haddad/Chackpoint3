<?php

namespace App\Service;

use App\Entity\Boat;
use App\Entity\Tile;
use App\Repository\TileRepository;

class MapManager
{
    private $tileRepository;

    public function __construct(TileRepository $tileRepository)
    {
        $this->tileRepository = $tileRepository;
    }
    public function tileExists($coordX, $coordY)
    {
        if ($coordX <= -1 ) {
            return false; 
        }
        if ( $coordX >= 12 ) {
            return false;
        }
        if ($coordY <= -1 ) {
            return false;
        }
        if ($coordY >= 6) {
            return false;
        }
         $tile = $this->tileRepository->findOneBy(['coordX' => $coordX, 'coordY' => $coordY]);

         if (!$tile) {
             return false;
         }
        return true;
    }

    public function getRandomIsland(): ?Tile
    {
        $islandTiles = $this->tileRepository->findBy(['type' => 'island', 'port']);
        if (empty($islandTiles)) {
            return null;
        }
        $randomIndex = array_rand($islandTiles);
        return $islandTiles[$randomIndex];
    }

    public function checkTreasure(Boat $boat)
    {
        $tile = $this->tileRepository->findOneBy([
            'coordX' => $boat->getCoordX(),
            'coordY' => $boat->getCoordY()
        ]);
        
       return $tile && $tile->getHasTreasure();
    }
}