<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{

    #[Route('/nous-contacter', name: 'app_contact')]

    public function index(Request $request): Response
    {
        
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $this->addFlash('notice','Merci de nous avoir contacté. Notre équipe va vous répondre dams les meilleurs délais.');

            //on pourra mettre ici une api de suivi de contact 
            //ou un envoi d email 


        }

        return $this->render('contact/contact.html.twig',[
            'form'=>$form->createView(),
        ]);
    }
}
