<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Doctrine\ORM\EntityManagerInterface;

final class Builder
{
    private $doctrine;

    // Injection des services via le constructeur
    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function mainMenu(FactoryInterface $factory, array $options): ItemInterface
    {
        $menu = $factory->createItem('root');

        $menu->addChild('Home', ['route' => 'app_home']);

        // Accédez aux services Doctrine directement sans utiliser le container
        $em = $this->doctrine;
        // Par exemple, trouvez un article de blog récent (si vous en avez besoin)
        // $blog = $em->getRepository(Blog::class)->findMostRecent();

        // Ajoutez des éléments au menu
        // $menu->addChild('Latest Blog Post', [
        //     'route' => 'blog_show',
        //     'routeParameters' => ['id' => $blog->getId()]
        // ]);

        return $menu;
    }

    public function breadcrumbMenu(FactoryInterface $factory, array $options): ItemInterface
{
    $menu = $factory->createItem('root');
    $menu->setChildrenAttributes(['class' => 'breadcrumb']);


    // Ajouter un lien "Home"
    $menu->addChild('Home', ['route' => 'app_home'])
        ->setAttribute('separator', '>');

    
    // Si une page actuelle est fournie dans les options, l'ajouter
    if (isset($options['current_page'])) {
        $menu->addChild($options['current_page'])
        ->setAttribute('separator', '>');

    }

    return $menu;
}

}
