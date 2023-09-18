<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class StripeController extends AbstractController
{
    

    #[Route('/commande/create-session/{reference} ', name: 'app_stripe_create_session')]

    public function index(EntityManagerInterface $entityMananger, Cart $cart, $reference): Response
    {
        $stripeSecretKey = $this->getParameter('STRIPE_SECRET_KEY');
        Stripe::setApiKey($stripeSecretKey);

        $product_for_stripe = [];
        $YOUR_DOMAIN = 'http://0.0.0.0:8083';

        $order = $entityMananger->getRepository(Order::class)->findOneByReference($reference);

        if (!$order) {
            $response = new JsonResponse(['error' => 'order']);
            return $response;
        }

        foreach ($order->getOrderDetails()->getValues() as $product) {
            $product_object = $entityMananger->getRepository(Product::class)->findOneByName($product->getProduct());
            $product_for_stripe[] =
                [
                    'price_data' =>
                    [
                        'currency' => 'EUR',
                        'unit_amount' => $product->getPrice(),
                        'product_data' =>
                        [
                            'name' => $product->getProduct(),
                            'images' => [$YOUR_DOMAIN . "/uploads/" . $product_object->getIllustration()],
                        ],
                    ],
                    'quantity' => $product->getQuantity(),
                ];
        }

        $product_for_stripe[] =
            [
                'price_data' =>
                [
                    'currency' => 'EUR',
                    'unit_amount' => $order->getCarrierPrice(),
                    'product_data' =>
                    [
                        'name' => $order->getCarrierName(),
                        'images' => [$YOUR_DOMAIN]
                    ],
                ],
                'quantity' => 1,
            ];

        /* $stripeSecretKey = 
         'sk_test_51MuftCK7QwzP0OK1hZmPieDZjDiyxmnwMxrDF5KOTruYX0iDRPaPW7b72LA97yHwpwxF4X529G2aUGaig1DgdC0v007MpWuMh2';  
        Stripe::setApiKey($stripeSecretKey); */

        $checkout_session = Session::create([
            'customer_email' => $this->getUser()->getEmail(),
            'payment_method_types' => ['card'],
            'line_items' => [[
                $product_for_stripe
            ]],
            'mode' => 'payment',
            'success_url' => $YOUR_DOMAIN . '/commande/merci/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $YOUR_DOMAIN . '/commande/erreur/{CHECKOUT_SESSION_ID}',
        ]);

        $order->setStripeSessionId($checkout_session->id);
        $entityMananger->flush();

        $response = new JsonResponse(['id' => $checkout_session->id]);
        return $response;
    }
}
