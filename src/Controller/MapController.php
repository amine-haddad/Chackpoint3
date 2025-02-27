<?php
namespace App\Controller;

use App\Entity\Tile;
use App\Repository\BoatRepository;
use App\Repository\TileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    /**
     * @Route("/map", name="map")
     */
    public function displayMap(BoatRepository $boatRepository): Response
    {
        $em    = $this->getDoctrine()->getManager();
        $tiles = $em->getRepository(Tile::class)->findAll();
        $map = [];

        foreach ($tiles as $tile) {
            $map[$tile->getCoordX()][$tile->getCoordY()] = $tile;
        }

        $boat         = $boatRepository->findOneBy([]);
        $boatTile     = $map[$boat->getCoordX()][$boat->getCoordY()] ?? null;
        $boatTileType = $boatTile ? $boatTile->getType() : 'Unknown';

        return $this->render('map/index.html.twig', [
            'map'          => $map,
            'boat'         => $boat,
            'boatTile'     => $boatTile,
            'boatTileType' => $boatTileType,
        ]);
    }

/**
 * @Route("/start", name="startGame")
 */
public function start(BoatRepository $boatRepository, TileRepository $tileRepository, EntityManagerInterface $entityManager)
{
    // Supprimer l'ancien trésor
    $tiles = $tileRepository->findBy(['hasTreasure' => true]);
    foreach ($tiles as $tile) {
        $tile->setHasTreasure(false);
        $entityManager->persist($tile);
    }
    $entityManager->flush(); // Flush après la boucle

    // Réinitialiser le bateau
    $boat = $boatRepository->findOneBy([]);
    $boat->setCoordX(0);
    $boat->setCoordY(0);
    $entityManager->persist($boat);

    // Choisir une nouvelle tuile d'île ou de port pour le trésor
    $islandTiles = $tileRepository->findBy(['type' => ['island', 'port']]); // Ajout du port
    if (empty($islandTiles)) {
        throw new \Exception("Aucune tuile valide trouvée pour le trésor !");
    }
    $randomIslandTile = $islandTiles[array_rand($islandTiles)];
    $randomIslandTile->setHasTreasure(true);
    $entityManager->persist($randomIslandTile);

    // Enregistrer les changements
    $entityManager->flush();

    return $this->redirectToRoute('map');
}

}
