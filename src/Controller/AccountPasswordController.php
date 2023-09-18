<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/compte/update-password', name: 'app_account_password')]

    public function index(Request $request, UserPasswordHasherInterface $encoder): Response
    {
        $notification = null;

        //on appelle user avec $this->getUser() pour avoir l uttilisateur courant 
        //on le stock dans $user
        $user = $this->getUser();

        //on creer le formulaire 
        //on lui donne le type (ChangePasswordType) en parametre et en deuxieme parametre on lui donne l objet de la class a laquelle le formulaire est lier ($user)
        $form = $this->createForm(ChangePasswordType::class, $user);

        //traitement du formulaire 
        //on effecture une ecoute de le requete via le handleRequest
        //et on lui donne en parametre la requete ($request)
        $form->handleRequest($request);

        //on verifie si le formulaire est soumis et valid
        if ($form->isSubmitted() && $form->isValid()) {

            //on recupere dans le formulaire l ancien password non crypter que l on stock dans $old_pwd
            $old_pwd = $form->get('old_password')->getData();

            //on uttilise la methode isPasswordValid pour comparer le nouveau et l ancien mot de passe
            //on lui donne en parametre le mot de passe encoder en base de donnee stocker dans $user et l ancien mot de passe $old_pwd
            if ($encoder->isPasswordValid($user, $old_pwd)) {
                $new_pwd = $form->get('new_password')->getData();
                $password = $encoder->hashPassword($user, $new_pwd);

                //on reinjecte le mot de passe hasher dans la variable $user par le biais du setpassword qui prend en parametre le $password
                $user->setPassword($password);
                //on appel entity mananger pour enregistrer (fluch) les donnees dans la bdd
                $this->entityManager->flush();

                $notification = "Votre mot de passe a bien ete mis a jour.";
            } else {
                $notification = "Votre mot de passe actuel n' est pas le bon";
            }
        }
        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification,
        ]);
    }
}
