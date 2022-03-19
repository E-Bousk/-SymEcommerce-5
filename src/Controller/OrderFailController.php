<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderFailController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * @Route("/commande/erreur/{stripeSessionId}", name="app_order_fail")
     */
    public function index($stripeSessionId): Response
    {
        /** @var Order $order */
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if (!$order || $order->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Envoyer un e-mail pour indiquer échec de paiement
        
        return $this->render('order/fail.html.twig', compact('order'));
    }
}
