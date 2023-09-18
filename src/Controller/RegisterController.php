<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/inscription', name: 'app_register')]

    public function index(Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $notification = null;

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        //on recupere les requetes du formulaire
        $form->handleRequest($request);

        //on verifie la soumission et la validation du formulaire
        if ($form->isSubmitted() && $form->isValid()) 
        {
            //on recupere toute les donnes du formulaire grace a getData que l on stock dans $user
            $user = $form->getData(); 

            //on verifie si l'uttilisateur existe deja
            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if(!$search_email)
            {
                //avec la method hashPassword on hash le mot de passe que l on stock dans $password
                //la methode hash password prend ici deux parametre l objet $user et le mot de passe saisi $user->getPassword();
                $password = $encoder->hashPassword($user, $user->getPassword());
    
                //on reinjecte le mot de passe hasher dans la variable $user par le biais du setpassword qui prend en parametre le $password
                $user->setPassword($password);
                //on appel entity mananger pour figer (persist) et enregistrer (fluch) les donnees dans la bdd
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                //envoie du mail
                $mail = new Mail();
                $content = 'Bonjour' .$user->getFirstname().
                "<br> Bienvenue Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse dolor eros, efficitur id nisi sed, suscipit euismod ante. Praesent dapibus lacus at placerat cursus. Pellentesque enim tortor, porttitor vitae cursus a, consectetur condimentum ante. Maecenas in vestibulum purus. Etiam nec risus ut est feugiat euismod. Nullam dignissim lorem vitae lacus egestas, in dapibus diam iaculis. Nullam faucibus est vel sollicitudin feugiat. Sed faucibus enim sed malesuada cursus. Donec tellus erat, molestie vitae enim sit amet, lobortis porta erat.";
                $mail->send($user->getEmail(),$user->getFirstname(),'Bienvenue sur My French Boutique',$content);

                //envoie de la notification
                $notification = "Votre inscription s'est correctement effectué. Vous pouvez dès a présent vous connecter à votre compte. ";
            }
            else 
            {
                $notification = "L'email que vous avez renseigner existe deja.";
            }


        }
        
        return $this->render('register/register.html.twig', 
        [
            //on creer la vue du formulaire 
            'form'=> $form->createView(),
            'notification' => $notification,

        ]);
    }
}
