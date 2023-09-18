<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/commande/merci/{stripeSessionId}', name: 'app_order_validate')]

    public function index(Cart $cart,$stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);
        if (!$order || $order->getUser() !== $this->getUser())
        {
            return $this->redirectToRoute('app_home');
        }

        if ($order->getState() == 0)
        {
            //vider la session 'cart'
            $cart->remove();
            //modification du status isPaid de notre commande en mettant 1
            $order->setState(1);
            $this->entityManager->flush();
            
            //envoyer un email au client pour lui confirmer sa commande
            $mail = new Mail();
            $content = 'Bonjour'.' '.$order->getUser()->getFirstname().
            "<br> Merci pour votre commande.";
            $mail->send($order->getUser()->getEmail(),$order->getUser()->getFirstname(),'Votre commande sur My French Boutique est bien validée',$content);
        }

        return $this->render('order_success/merci.html.twig',[
            'order'=> $order,
        ]);
    }
}
