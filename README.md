# Gîte du Rain du Pair — Projet CDA

Ce projet est une application web de réservation pour le gîte du Rain du Pair,
développée dans le cadre du titre professionnel Concepteur Développeur d’Applications (CDA).

L’objectif principal est de permettre aux visiteurs de réserver un séjour de manière simple et sécurisée, 
tout en offrant à l’administrateur du gîte une interface de gestion pour suivre les réservations et personnaliser l’offre.

---

## Fonctionnalités dévelopées

- Authentification sécurisée 
- Processus de réservation complet :
  - Recherche de dates disponibles
  - Formulaire de réservation
  - Paiement en ligne
  - Notification de confirmation par SMS
- Annulation d’une réservation
- Ajout d’extras lors d’une réservation
- Ajout d’un avis après un séjour
- Tableau de bord utilisateur
- Tableau de bord administrateur
- Affichage des derniers posts Instagram
- Formulaire de contact
- API REST "Actigo" : suggestions d’activités touristiques autour du gîte (via Next.js + Clerk)
- Page FAQ
- Ajout de codes promotionnels
- Tarification dynamique selon les saisons
- Plan du site

---

## Stack technique

| Côté | Technologie |
|------|-------------|
| Backend | Symfony 7, Doctrine ORM |
| Frontend (serveur Symfony) | Twig + TailwindCSS |
| Authentification | Symfony Security |
| Paiement | Stripe |
| Base de données | MySQL |
| API complémentaire | Next.js, Prisma + Clerk (Actigo API) |

---

## Tests

- Données de développement disponibles via des fixtures Symfony (utilisateurs, réservations, périodes, etc.)
- Tests unitaires et fonctionnels réalisés avec **PHPUnit**
- Couverture des tests : entités, services et contrôleurs critiques
- Commande d’exécution des tests :

```bash
php bin/phpunit