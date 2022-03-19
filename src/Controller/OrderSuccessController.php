<?php

namespace App\Controller;

use App\Entity\Order;
use App\Util\Cart;
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
    public function index($stripeSessionId, Cart $cart): Response
    {
        /** @var Order $order */
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);
        
        if (!$order || $order->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if (!$order->getIsPaid()) {
            // Vide le panier
            $cart->removeCart();

            // Modifier le statut « isPAid »
            $order->setIsPaid(true);
            $this->entityManager->flush();

            // Envoyer un email au client pour lui confirmer se commande
        }

        return $this->render('order/success.html.twig', compact('order'));
    }
}
