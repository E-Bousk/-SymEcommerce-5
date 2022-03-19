<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeController extends AbstractController
{
    /**
     * @Route("/commande/create-session/{reference}", name="app_stripe_create_session")
     */
    public function index($reference, EntityManagerInterface $entityManager): JsonResponse
    {
        /** @var Order $order */
        $order = $entityManager->getRepository(Order::class)->findOneByReference($reference);

        if (!$order) {
            return new JsonResponse(['error' => 'order']);
        }

        $YOUR_DOMAIN = 'https://127.0.0.1:8000';
        Stripe::setApiKey('sk_test_51Js6ShGPYQdv0649MRytKynKAVK7AqL60JKfuPf5Pd0L2f0DBHzlOknpqWeXLoiQFGohJJIXdePxcAcNDL4x39sr00vXgb65Qd');

        $productForStripe = [];

        // Ajoute les produits à STRIPE
        foreach ($order->getOrderDetails()->getValues() as $product) {
            $product_object = $entityManager->getRepository(Product::class)->findOneByName($product->getProduct());

            $productForStripe[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => $product->getPrice(),
                    'product_data' => [
                      'name'   => $product->getProduct(),
                      'images' => [sprintf('%s/uploads/images/%s', $YOUR_DOMAIN, $product_object->getIllustration())]
                    ],
                ],
                'quantity'   => $product->getQuantity()
            ];
        }

        // Ajoute le transporteur à STRIPE
        $productForStripe[] = [
            'price_data' => [
                'currency'     => 'eur',
                'unit_amount'  => $order->getCarrierPrice(),
                'product_data' => [
                  'name'   => $order->getCarrierName(),
                //   'images' => [sprintf('%s/uploads/images/transporteur.png', $YOUR_DOMAIN)]
                ],
            ],
            'quantity'   => 1
        ];

        // Créé la session STRIPE
        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'mode'                 => 'payment',
            'customer_email'       => $order->getUser()->getEmail(),
            'line_items'           => [
                $productForStripe
            ],
            'success_url'          => sprintf('%s/commande/succes/{CHECKOUT_SESSION_ID}', $YOUR_DOMAIN),
            'cancel_url'           => sprintf('%s/commande/erreur/{CHECKOUT_SESSION_ID}', $YOUR_DOMAIN)
        ]);

        // Sauvegarde en BDD l' ID de session STRIPE pour gérer 'succès' ou 'erreur' pages
        $order->setStripeSessionId($checkout_session->id);
        $entityManager->flush();


        return new JsonResponse(['id' => $checkout_session->id]);         
    }
}
