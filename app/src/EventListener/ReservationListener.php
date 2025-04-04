<?php 

namespace App\EventListener;

use Twig\Environment;
use App\Entity\Reservation;
use App\Service\SendEmailService;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsDoctrineListener(event: 'postPersist')]
final class ReservationListener
{
    public function __construct(
        private SendEmailService $mailer,
        private Environment $twig,
        private ParameterBagInterface $params
    ) {}

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Reservation) {
            return;
        }

        $reservation = $entity;
        $gite = $reservation->getGite();
        $projectDir = $this->params->get('kernel.project_dir');
        $logoPath = $projectDir . '/public/assets/img/logo-gite-rain-du-pair.png';
        $logo = $this->imageToBase64($logoPath);

        // Mail au client
        $this->mailer->send(
            'contact@gite-rain-du-pair.fr',
            $reservation->getEmail(),
            '[GITE RAIN DU PAIR] Confirmation de demande de réservation',
            'request_reservation',
            [
                'reservation' => $reservation,
                'logo' => $logo,
            ]
        );

        // Mail à l’admin
        $this->mailer->sendAdminNotification(
            'contact@gite-rain-du-pair.fr',
            'contact@gite-rain-du-pair.fr',
            'Nouvelle réservation reçue',
            'admin_request_reservation',
            [
                'reservation' => $reservation,
            ]
        );
    }

    private function imageToBase64(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }

        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}
