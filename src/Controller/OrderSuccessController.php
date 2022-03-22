<?php

namespace App\Controller;

use App\Entity\Order;
use App\Util\Cart;
use App\Util\SendEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/succes/{stripeSessionId}", name="app_order_success")
     */
    public function index($stripeSessionId, Cart $cart, SendEmail $sendEmail): Response
    {
        /** @var Order $order */
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);
        
        if (!$order || $order->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($order->getState() === 0) {
            // Vide le panier
            $cart->removeCart();

            // Modifier le statut
            $order->setState(1); // 1 = paiement accepté
            $this->entityManager->flush();

            // Envoyer un email au client pour lui confirmer se commande
            $sendEmail->send(
                $order->getUser()->getEmail(),
                $order->getUser()->getFullName(),
                'Votre commande la e-boutique est bien validée',
                sprintf('Bonjour %s<br>Merci pour votre commande<br>Lorem ipsum dolor sit amet consectetur adipisicing elit. Perspiciatis cupiditate vitae modi officia deleniti sapiente at nihil quod error! Odio veniam autem enim suscipit ut. Hic repellendus doloremque rerum pariatur?', $order->getUser()->getFirstname())
            );
        }

        return $this->render('order/success.html.twig', compact('order'));
    }
}
