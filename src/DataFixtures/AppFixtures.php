<?php

/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 23/11/18
 * Time: 11:29
 */

 namespace App\DataFixtures;

use App\Entity\Boat;
use App\Entity\Tile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $tiles = [
            ['sea', 'sea', 'sea', 'sea', 'sea', 'island', 'sea', 'sea', 'sea', 'port', 'sea', 'sea'],
            ['sea', 'port', 'sea', 'island', 'sea', 'sea', 'sea', 'sea', 'sea', 'sea', 'island', 'sea'],
            ['sea', 'sea', 'sea', 'sea', 'sea', 'sea', 'sea', 'sea', 'island', 'sea', 'sea', 'sea'],
            ['sea', 'island', 'sea', 'sea', 'island', 'sea', 'sea', 'sea', 'sea', 'sea', 'sea', 'sea'],
            ['sea', 'sea', 'sea', 'sea', 'sea', 'sea', 'sea', 'island', 'sea', 'sea', 'port', 'sea'],
            ['island', 'sea', 'sea', 'sea', 'port', 'sea', 'sea', 'sea', 'sea', 'sea', 'sea', 'island'],
        ];

        $tileEntities = [];

        foreach ($tiles as $y => $line) {
            foreach ($line as $x => $type) {
                $tile = new Tile();
                $tile->setType($type);
                $tile->setCoordX($x);
                $tile->setCoordY($y);
                
                $tile->setHasTreasure(false);
                $manager->persist($tile);
                
                // Stocker chaque tuile créée
                $tileEntities[] = $tile;
            }
        }

        // Définir un nombre de trésors
        $numberOfTreasures = 3; // Par exemple, placer 3 trésors

        // Assigner un trésor à des tuiles aléatoires
        for ($i = 0; $i < $numberOfTreasures; $i++) {
            $randomTile = $tileEntities[array_rand($tileEntities)];
            $randomTile->setHasTreasure(true); // Assigner un trésor à une tuile aléatoire
        }

        // Créer le bateau
        $boat = new Boat();
        $boat->setCoordX(0);
        $boat->setCoordY(0);
        $boat->setName('Black Pearl');
        $manager->persist($boat);

        // Valider les changements dans la base de données
        $manager->flush();
    }
}
