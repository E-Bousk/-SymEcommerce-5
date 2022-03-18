<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use App\Util\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/commande", name="app_order")
     */
    public function index(Cart $cart): Response
    {
        if (!$this->getUser()->getAddresses()->getValues()) {
            return $this->redirectToRoute('app_account_address_add');
        }

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getDataOnCart()
        ]);
    }

    /**
     * @Route("/commande/recapitulatif", name="app_order_recap", methods={"POST"})
     */
    public function add(Request $request, Cart $cart): Response
    {
        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $date = new \DateTime();
            $carrier = $form->get('carriers')->getData();
            $delivery = $form->get('addresses')->getData();

            $deliveryContent = $delivery->getFirstname() . ' ' . $delivery->getLastname();
            $deliveryContent .= '<br />' . $delivery->getPhone();
            if ($delivery->getCompany()) $deliveryContent .= '<br />' . $delivery->getCompany();
            $deliveryContent .= '<br />' . $delivery->getAddress();
            $deliveryContent .= '<br />' . $delivery->getPostal(). ' ' . $delivery->getCity(). ' (' . $delivery->getCountry() . ')';
            


            // enregistrement de la commande dans « Order »
            $order = (new Order())
                                ->setUser($this->getUser())
                                ->setCreatedAt($date)
                                ->setCarrierName($carrier->getName())
                                ->setCarrierPrice($carrier->getPrice())
                                ->setDelivery($deliveryContent)
                                ->setIsPaid(false)
            ;
            $this->entityManager->persist($order);

            // enregistrement des produits dans « OrderDetails »   
            foreach ($cart->getDataOnCart() as $product) {
                $orderDetails = (new OrderDetails())
                                                ->setCustomerOrder($order)
                                                ->setProduct($product['product']->getName())
                                                ->setQuantity($product['quantity'])
                                                ->setPrice($product['product']->getPrice())
                                                ->setTotal($product['product']->getPrice() * $product['quantity'])
                ;
                $this->entityManager->persist($orderDetails);
            }

            $this->entityManager->flush();

            return $this->render('order/add.html.twig', [
                'cart'    => $cart->getDataOnCart(),
                'carrier' => $carrier,
                'delivery' => $deliveryContent
            ]);
        }

        return $this->redirectToRoute('app_cart');
    }
}
