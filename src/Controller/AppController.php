<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="app_redirect_index")
     */
    public function redirectIndex()
    {
        return $this->redirectToRoute('app_index');
    }
    
    /**
     * @Route("/index", name="app_index")
     */
    public function index()
    {
        return $this->render('app/index.html.twig', [
        ]);
    }
}
