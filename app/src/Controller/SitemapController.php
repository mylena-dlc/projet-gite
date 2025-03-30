<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SitemapController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }


    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml', '_public_access' => true])]
    public function index(Request $request): Response
    {
        // On récupère le nom d'hôte depuis l'URL
        $hostname = $request->getSchemeAndHttpHost();

        // On initialise un tableau pour lister les URLs
        $urls = [];

        // On ajoute les URLs "statiques"
        $urls[] = ['loc' => $this->generateUrl('app_home')];
        $urls[] = ['loc' => $this->generateUrl('app_search_reservation')];
        $urls[] = ['loc' => $this->generateUrl('app_reservation')];
        $urls[] = ['loc' => $this->generateUrl('new_reservation')];
        $urls[] = ['loc' => $this->generateUrl('app_galery')];
        $urls[] = ['loc' => $this->generateUrl('app_login')];
        $urls[] = ['loc' => $this->generateUrl('app_register')];
        $urls[] = ['loc' => $this->generateUrl('app_contact')];
        $urls[] = ['loc' => $this->generateUrl('app_faq')];
        $urls[] = ['loc' => $this->generateUrl('app_mentions_legales')];
        $urls[] = ['loc' => $this->generateUrl('app_politique_confidentialite')];
        $urls[] = ['loc' => $this->generateUrl('app_condition_generales_vente')];
        $urls[] = ['loc' => $this->generateUrl('app_sitemap')];

        // Fabrication de la réponse
        $response = new Response(
            $this->renderView('home/sitemap.html.twig', [
                'urls' => $urls,
                'hostname' => $hostname
            ])
        );
        
        // Ajout des entêtes HTTP
        $response->headers->set('Content-Type', 'text/xml');

        // On envoie la réponse
        return $response;
    }
}