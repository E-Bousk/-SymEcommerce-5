<?php

namespace App\Controller;

use App\Util\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/mon-panier", name="app_cart")
     */
    public function index(Cart $cart): Response
    {
        return $this->render('cart/index.html.twig', [
            'cart' => $cart->getDataOnCart()
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="app_cart_add")
     */
    public function add($id, Cart $cart): Response
    {
        $cart->addOnCart($id);

        return $this->redirectToRoute('app_cart');
    }

    /**
     * @Route("/cart/decrement/{id}", name="app_cart_decrement")
     */
    public function decrement($id, Cart $cart): Response
    {
        $cart->decrementOnCart($id);

        return $this->redirectToRoute('app_cart');
    }

    /**
     * @Route("/cart/delete/{id}", name="app_cart_delete")
     */
    public function delete($id, Cart $cart): Response
    {
        $cart->deleteOnCart($id);

        return $this->redirectToRoute('app_cart');
    }

    /**
     * @Route("/cart/remove", name="app_cart_remove")
     */
    public function remove(Cart $cart): Response
    {
        $cart->removeCart();

        return $this->redirectToRoute('app_products');
    }
}
