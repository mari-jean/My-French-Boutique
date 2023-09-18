<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande', name: 'app_order')]

    public function index(Cart $cart, Request $request): Response
    {

        if (!$this->getUser()->getAddresses()->getValues()) {
            return $this->redirectToRoute('app_account_address_add');
        }

        $form = $this->createForm(
            OrderType::class,
            null,
            [
                'user' => $this->getUser()
            ]
        );

        return $this->render(
            'order/order.html.twig',
            [
                'form' => $form->createView(),
                'cart' => $cart->getFull(),
            ]
        );
    }

    #[Route('/commande/recapitulatif', name: 'app_order_recap')]

    public function add(Cart $cart, Request $request): Response
    {

        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $date = new DateTime();
            $carriers = $form->get('carriers')->getData();

            $delivery = $form->get('addresses')->getData();
            $delivery_content = $delivery[0]->getFirstname() . ' ' . $delivery[0]->getLastname();
            $delivery_content .= '<br>' . $delivery[0]->getPhone();

            if ($delivery[0]->getCompagny()) 
            {
                $delivery_content .= '<br>' . $delivery[0]->getCompagny();
            }
            $delivery_content .= '<br>' . $delivery[0]->getAddress();
            $delivery_content .= '<br>' . $delivery[0]->getPostal() . ' ' . $delivery[0]->getcity();
            $delivery_content .= '<br>' . $delivery[0]->getCountry();

            //Enregistrer sa commande order()
            $order = new Order();

            $reference = $date->format('dmY').'-'.uniqid();
            $order->setReference($reference);

            $order->setUser($this->getUser());
            $order->setCreatedAt($date);
            $order->setCarrierName($carriers[0]->getName());
            $order->setCarrierPrice($carriers[0]->getPrice());
            $order->setDelivery($delivery_content);
            $order->setState(0);
            
                //0 non validee
                // 1 payee
                // 2 preparation en cours
                // 3 livraison en cours
            ;
            $this->entityManager->persist($order);

            //Enregistrer mes produits OrderDetails()
            foreach ($cart->getFull() as $product) 
            {
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order);
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);
                $this->entityManager->persist($orderDetails);
            }

            $this->entityManager->flush();

            return $this->render(
                'order/add.html.twig',
                [
                    'cart' => $cart->getFull(),
                    'carrier' => $carriers,
                    'delivery' => $delivery,
                    'reference' => $order->getReference(),
                ]
            );
        }
        return $this->redirectToRoute('app_cart');
    }
}

