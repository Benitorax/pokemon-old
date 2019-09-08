<?php

namespace App\Controller;

use App\Form\AdventureCommandType;
use App\Handler\AdventureHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdventureController extends AbstractController
{
    /**
     * @Route("/adventure/", name="adventure_index")
     */
    public function index(Request $request, AdventureHandler $adventureHandler)
    {
        $form = $this->createForm(AdventureCommandType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $adventureHandler->handle($form->getClickedButton()->getName(), $form);
            //dd($data);
            return $this->render('adventure/index.html.twig', [
                'form' => $form->createView(),
                'opponent' => $data['opponent'],
                'player' => $data['player'],
                'messages' => $data['messages'],
            ]);
        }

        $adventureHandler->clear();

        return $this->render('adventure/index.html.twig', [
            'form' => $form->createView(),
            'opponent' => null,
            'player' => null,
            'messages' => null
        ]);
    }
}
