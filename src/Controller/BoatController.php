<?php
namespace App\Controller;

use App\Entity\Boat;
use App\Repository\BoatRepository;
use App\Repository\TileRepository;
use App\Service\MapManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/boat")
 */
class BoatController extends AbstractController
{
    private $mapManager;
    private function validateCoordinates(int $x, int $y): bool
    {
        return $this->mapManager->tileExists($x, $y);
    }

    public function __construct(MapManager $mapManager)
    {
        $this->mapManager = $mapManager;
    }
    /**
     * Move the boat to coord x,y
     * @Route("/move/{x}/{y}", name="moveBoat", requirements={"x"="\d+", "y"="\d+"}))
     */
    public function moveBoat(int $x, int $y, BoatRepository $boatRepository, EntityManagerInterface $em): Response
    {
        if (! $this->validateCoordinates($x, $y)) {
            // Si les coordonnées sont invalides, rediriger avec un message d'erreur
            $this->addFlash('error', 'Les coordonnées sont invalides, le bateau ne peut pas se déplacer en dehors de la zone!');
            return $this->redirectToRoute('map', ['error' => 'Coordonnées invalides!']);
        }
    

        $boat = $boatRepository->findOneBy([]);
        $boat->setCoordX($x);
        $boat->setCoordY($y);
        $em->flush();
        return $this->redirectToRoute('map');
    }

    /**
     * Move the boat in a direction
     * @Route("/direction/{direction}", name="moveDirection", requirements={"direction"="N|S|E|W"})
     */
    public function moveDirection(string $direction, BoatRepository $boatRepository, TileRepository $tileRepository, EntityManagerInterface $entityManager)
    {
        $boat = $boatRepository->findOneBy([]);
        $x    = $boat->getCoordX();
        $y    = $boat->getCoordY();

        //déplacement avec switch
        switch ($direction) {
            case 'N':
                $newY = $y - 1;
                $newX = $x;
                break;
            case 'S':
                $newY = $y + 1;
                $newX = $x;
                break;
            case 'E':
                $newY = $y;
                $newX = $x + 1;
                break;
            case 'W':
                $newY = $y;
                $newX = $x - 1;
                break;
            default:
                throw new \InvalidArgumentException("Direction non valide !");
        }

        // Utilisation de la fonction validateCoordinates() pour vérifier les nouvelles coordonnées
    if (! $this->validateCoordinates($newX, $newY)) {
        // Si les coordonnées sont invalides, rediriger avec un message d'erreur
        $this->addFlash('error', 'Les coordonnées sont invalides, le bateau ne peut pas se déplacer en dehors de la zone!');
        return $this->redirectToRoute('map', ['error' => 'Coordonée invalide tu vas tombé dans le vide!']);
    }

        //position est valide?
        $foundTreasure = $this->mapManager->checkTreasure($boat);
        if ($foundTreasure) {
            $this->addFlash('success', 'Trésor trouvé !');
        } else {
            $this->addFlash('error', 'Pas de trésor à cet endroit.');
        }

        

        $boat->setCoordX($newX);
        $boat->setCoordY($newY);
        // Vérifier si le bateau est sur un trésor
        $tile = $tileRepository->findOneBy(['coordX' => $newX, 'coordY' => $newY]);

        if ($tile && $tile->getHasTreasure()) {
            // Enlever le trésor de la tuile (si nécessaire)
            $tile->setHasTreasure(false);
            $entityManager->persist($tile);

            // Ajouter un message de succès
            $this->addFlash('success', 'Vous avez trouvé le trésor! 🏴‍☠️');

            // Placer un nouveau trésor sur une autre tuile aléatoire
            $islandTiles = $tileRepository->findBy(['type' => ['island', 'port']]);
            $randomIslandTile = $islandTiles[array_rand($islandTiles)];
            $randomIslandTile->setHasTreasure(true);
            $entityManager->persist($randomIslandTile);
        }

        $entityManager->flush();
        return $this->redirectToRoute('map');
    }

}
