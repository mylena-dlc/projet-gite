<?php

namespace App\Controller;


use Knp\Menu\FactoryInterface;
use App\HttpClient\ApiHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ActivityController extends AbstractController
{
    private ApiHttpClient $apiHttpClient;

    public function __construct(ApiHttpClient $apiHttpClient)
    {
        $this->apiHttpClient = $apiHttpClient;
    }
    #[Route('/activity', name: 'app_activity')]
    public function index(FactoryInterface $factory): Response
    {
        $breadcrumb = $factory->createItem('root');
        $breadcrumb->addChild('Accueil', ['route' => 'app_home']);
        $breadcrumb->addChild('Aux alentours'); 

        $description = "Explorez les environs du gîte : randonnée au Lac Blanc, ski, VTT, visites de villages alsaciens et nature préservée. Découvrez les activités à faire près d'Orbey.";

        $categories = $this->apiHttpClient->getCategories();
        
        return $this->render('home/activity.html.twig', [
            'categories' => $categories,
            'breadcrumb' =>$breadcrumb,
            'description' => $description
        ]);

    }

}