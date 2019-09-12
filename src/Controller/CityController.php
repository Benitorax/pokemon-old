<?php

namespace App\Controller;

use App\Form\InfirmaryType;
use App\Form\ShopType;
use App\Handler\CityHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CityController extends AbstractController
{
    /**
     * @Route("/city/", name="city")
     */
    public function index(Request $request, CityHandler $cityHandler)
    {
        $shopForm = $this->createForm(ShopType::class);
        $infirmaryForm = $this->createForm(InfirmaryType::class);

        if($request->isMethod('POST')) {
            $shopForm->handleRequest($request);
            $infirmaryForm->handleRequest($request);

            if($shopForm->isSubmitted() && $shopForm->isValid()) {
                $cityHandler->handleShopForm($shopForm);

            } elseif($infirmaryForm->isSubmitted() && $infirmaryForm->isValid()) {
                $cityHandler->handleInfirmaryForm($infirmaryForm);
            }
        }

        return $this->render('city/index.html.twig', [
            'shopForm' => $shopForm->createView(),
            'infirmaryForm' => $infirmaryForm->createView()
        ]);
    }
}
