<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TournamentController extends AbstractController
{
    /**
     * @Route("/tournament/", name="tournament")
     */
    public function index(Request $request)
    {
        return $this->render('tournament/index.html.twig', [
        ]);
    }
}
