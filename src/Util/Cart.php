<?php

namespace App\Util;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{
    private SessionInterface $session;
    private EntityManagerInterface $entityManager;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
    }

    public function getCart()
    {
        return $this->session->get('cart', []);
    }

    public function getDataOnCart()
    {
        $dataOnCart = [];

        if ($this->getCart()) {
            foreach ($this->getCart() as $id => $quantity) {
                $product = $this->entityManager->getRepository(Product::class)->findOneById($id);
                if (!$product) {
                    $this->deleteOnCart($id);
                    continue;
                }
                
                $dataOnCart[] = [
                    'product'  => $product,
                    'quantity' => $quantity
                ];
            }
        }

        return $dataOnCart;
    }

    public function addOnCart($id)
    {
        $cart = $this->getCart();
        
        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->session->set('cart', $cart);
    }

    public function decrementOnCart($id)
    {
        $cart = $this->getCart();
        
        if ($cart[$id] > 1) {
            $cart[$id]--;
        } else {
            unset($cart[$id]);
        }

        $this->session->set('cart', $cart);
    }

    public function deleteOnCart($id)
    {
        $cart = $this->getCart();

        unset($cart[$id]);
        
        return $this->session->set('cart', $cart);
    }

    public function removeCart()
    {
        return $this->session->remove('cart');
    }
}
