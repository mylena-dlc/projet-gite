<?php

namespace App\Controller;

use App\Entity\Token;
use App\Form\TokenType;
use App\Repository\GiteRepository;
use App\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/token')]
final class TokenController extends AbstractController
{
    #[Route('/', name: 'app_token_index', methods: ['GET', 'POST'])]
    public function index(
        TokenRepository $tokenRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        GiteRepository $giteRepository
    ): Response|JsonResponse {
        // Récupération des tokens existants pour les affichages
        $findActiveTokens = $tokenRepository->findActiveTokens();
        $findInactiveTokens = $tokenRepository->findInactiveTokens();
        $findExpirateTokens = $tokenRepository->findExpirateTokens();
    
        // Récupération du gîte associé
        $gite = $giteRepository->find(1);
    
        // Gestion du formulaire d'ajout d'un nouveau token
        $token = new Token();
        $form = $this->createForm(TokenType::class, $token);
        $form->handleRequest($request);
    
        // Gestion AJAX pour l'ajout d'un token
        if ($request->isXmlHttpRequest() && $form->isSubmitted() && $form->isValid()) {
            // Associer le token au gîte
            $token->setGite($gite);
            $token->setIsActive(false);
            $entityManager->persist($token);
            $entityManager->flush();
    
            // Retourner une réponse JSON
            return new JsonResponse([
                'success' => true,
                'token' => [
                    'id' => $token->getId(),
                    'code' => $token->getCode(),
                    'discount' => $token->getDiscount(),
                    'expirationDate' => $token->getExpirationDate()->format('d-m-Y'),
                    'isActive' => $token->isActive(),
                ],
                'csrfToken' => $this->container->get('security.csrf.token_manager')->getToken('delete' . $token->getId())->getValue(),
            ]);
        }
    
        // Gestion classique pour un affichage initial
        if ($form->isSubmitted() && $form->isValid()) {
            $token->setGite($gite);
            $entityManager->persist($token);
            $entityManager->flush();
            $this->addFlash('success', 'Code promo ajouté avec succès !');
    
            return $this->redirectToRoute('app_token_index');
        }
    
        return $this->render('token/index.html.twig', [
            'findActiveTokens' => $findActiveTokens,
            'findInactiveTokens' => $findInactiveTokens,
            'findExpirateTokens' => $findExpirateTokens,
            'form' => $form->createView(),
        ]);

    }

    #[Route('/{id}/delete', name: 'app_token_delete', methods: ['POST'])]
        public function delete(Request $request, Token $token, EntityManagerInterface $entityManager): Response
        {
            if ($this->isCsrfTokenValid('delete'.$token->getId(), $request->getPayload()->getString('_token'))) {
                $entityManager->remove($token);
                $entityManager->flush();
                $this->addFlash('success', 'Le code promo a bien été supprimé.');

            } else {
                $this->addFlash('error', 'Erreur lors de la suppression du token.');

            }

            return $this->redirectToRoute('app_token_index', [], Response::HTTP_SEE_OTHER);
        }


        #[Route('/activate/{id}', name: 'app_token_active', methods: ['POST'])]
        public function activateToken(int $id, Request $request, EntityManagerInterface $entityManager, TokenRepository $tokenRepository, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $token = $tokenRepository->find($id);

            if (!$token) {
                return new JsonResponse(['success' => false, 'error' => 'Token introuvable.'], 404);
            }

            $token->setIsActive(true);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'token' => [
                    'id' => $token->getId(),
                    'code' => $token->getCode(),
                    'discount' => $token->getDiscount(),
                    'expirationDate' => $token->getExpirationDate()->format('d-m-Y'),
                ],
                'csrfToken' => $csrfTokenManager->getToken('delete' . $token->getId())->getValue(),
            ]);
        }

        throw $this->createNotFoundException('Requête invalide.');
    }

}
