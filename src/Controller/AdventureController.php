<?php

namespace App\Controller;

use App\Form\Command\TravelType;
use App\Handler\AdventureHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdventureController extends AbstractController
{
    /**
     * @Route("/adventure/", name="adventure")
     */
    public function index(Request $request, AdventureHandler $adventureHandler)
    {
        if($request->isMethod('POST')) {
            $data = $adventureHandler->handleRequest($request);

            return $this->render('adventure/index.html.twig', [
                'form' => $data['form']->createView(),
                'opponent' => $data['opponent'],
                'player' => $data['player'],
                'messages' => $data['messages'],
                'centerImageUrl' => $data['centerImageUrl'] ?? null
            ]);
        }

        $adventureHandler->clear();
        $form = $this->createForm(TravelType::class);

        return $this->render('adventure/index.html.twig', [
            'form' => $form->createView(),
            'opponent' => null,
            'player' => null,
            'messages' => null
        ]);
    }
}
