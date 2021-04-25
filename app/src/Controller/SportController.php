<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Sport;
use App\Entity\TeamPlayer;

class SportController extends AbstractController
{
	 /**
     * @Route("/sport", name="sport")
     */
    public function sport()
    {
 		$sport = $this->getDoctrine()
        ->getRepository(Sport::class)->findAll();

        return $this->render('sport/index.html.twig', array(
            'sport' => $sport,
        ));
    }

   	 /**
     * @Route("/team_player/rand", name="team_player_rand")
     */
    public function player()
    {
    	$number = random_int(0, 100);
 		$player = $this->getDoctrine()
        ->getRepository(TeamPlayer::class)->find($number);
        $sport = $this->getDoctrine()
        ->getRepository(Sport::class)->findBy(['id' => $player->getSport()]);

        return $this->render('player/index.html.twig', array(
            'player' => $player, 'sport' => $sport[0],
        ));
    }
}