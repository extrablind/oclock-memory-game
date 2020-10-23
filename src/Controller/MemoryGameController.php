<?php

namespace App\Controller;

use App\Entity\Memory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MemoryGameController extends AbstractController
{
    // Dependency injection : auto load class from construct function
    public function __construct(EntityManagerInterface $em)
    {
        // em is entity manager : Doctrine which manages databases table and fields
        $this->em        = $em;
    }

    /**
     * @Route("/", name="home")
     * @Route("/game/oclock-memory", name="oclock_memory_game")
     */
    // This render the template in ./templates/Game/memory-game.html.twig which is loading our vuejs app
    public function game(Request $request)
    {
        return $this->render('Game/memory-game.html.twig');
    }

    /**
     * @Route("/game/oclock-memory/save", name="oclock_memory_game_save_score")
     */
    // Ajax POST method to save in db score at the gameOver or win
    // Request is auto injected in params
    public function saveScore(Request $request)
    {
        // Retrieve POST params
        // For username, only keep 3 letters to remember old school flipper game scoring system :)
        $username  = substr($request->request->get('username'), 0, 3);
        $score     = $request->request->get('score');
        // Convert to integer the string sended or db will complain as it define integer type
        $time      = (int) $request->request->get('time');
        // Initialize a datetime object with default to current time
        $date     = new \DateTime();
        // Create a fresh entity yo gather POST params ans save in db
        $newScore = new Memory();
        $newScore
            ->setName($username)
            ->setDate($date)
            ->setScore($score)
            ->setTime($time);

        // Save in db
        $this->em->persist($newScore);
        $this->em->flush();

        // Return a simple message in json format
        return new JsonResponse(['message' => 'Saved']);
    }

    /**
     * @Route("/game/oclock-memory/high-score", name="oclock_memory_game_get_high_score")
     */
    // Ajax GET method to retrieve high score from db
    public function getLastHighScores()
    {
        // See MemoryRepositories to add or delete methods fetching to db
        $query    = $this->em->getRepository(Memory::class)->findHighScores(10);
        $scores   = $query->getResult(Query::HYDRATE_ARRAY);

        return new JsonResponse($scores);
    }
}
