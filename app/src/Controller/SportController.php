<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Sport;

class SportController extends AbstractController
{
	 /**
     * @Route("/sport", name="sport")
     */
    public function number()
    {
 		$sport = $this->getDoctrine()
        ->getRepository(Sport::class)->findAll();

        return $this->render('sport/index.html.twig', array(
            'sport' => $sport,
        ));
    }
}