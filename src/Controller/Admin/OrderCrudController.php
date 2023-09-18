<?php

namespace App\Controller\Admin;

use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;


class OrderCrudController extends AbstractCrudController
{

    private $entityManager;
    private $crudUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $crudUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->crudUrlGenerator = $crudUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        //custom action qui permet de modifier le statue de la commande dans la partie  gestion admin 
        $updatePreparation = Action::new('updatePreparation', 'Préparation en cours','fa fa-box-open')->linkToCrudAction('updatePreparation');
        $updateDelivery = Action::new('updateDelivery', 'Livraison en cours', 'fas fa-truck')->linkToCrudAction('updateDelivery');
        return $actions
        ->add('index', 'detail')
        ->add('detail', $updatePreparation)
        ->add('detail', $updateDelivery);
    }

    //fonction pour mettre a jour le statut de la preparation 
    public function updatePreparation(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();
        $order->setState(2);
        $this->entityManager->flush();
        $this->addFlash('notice', "<span style='color:green;'><strong>La commande ".$order->getReference()." est bien <i><u>en cours de préparation</u></i>.</strong></span>");
        $url = $this->crudUrlGenerator
            ->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();

        //envoyer un email au client pour lui confirmer la preparation en cour de sa commande
        $mail = new Mail();
        $content = 'Bonjour'.' '.$order->getUser()->getFirstname()."<br>";
        $mail->send($order->getUser()->getEmail(),$order->getUser()->getFirstname(),'Votre commande sur My French Boutique est en cours de préparation',$content);
        return $this->redirect($url);
    }

    //fonction pour mettre a jour le statut de la livraison
    public function updateDelivery(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();
        $order->setState(3);
        $this->entityManager->flush();
        $this->addFlash('notice', "<span style='color:orange;'><strong>La commande ".$order->getReference()." est bien <i><u>en cours de livraison</u></i>.</strong></span>");
        $url = $this->crudUrlGenerator
            ->setController(OrderCrudController::class)
            ->setAction('index')
            ->generateUrl();

        //envoyer un email au client pour lui confirmer la livraison en cour de sa commande
        $mail = new Mail();
        $content = 'Bonjour'.' '.$order->getUser()->getFirstname()."<br>";
        $mail->send($order->getUser()->getEmail(),$order->getUser()->getFirstname(),'Votre commande sur My French Boutique est en cours de livraison',$content);
        return $this->redirect($url);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id'=> 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateTimeField::new('createdAt', 'Passée le'),
            TextField::new(('user.getFullname'), 'Uttilisateur'),
            TextEditorField::new('delivery', 'Adresse de livraison')->onlyOnDetail(),
            MoneyField::new('total','Total Produit')->setCurrency('EUR'),
            TextField::new('carrierName', 'Transpoteur'),
            MoneyField::new('carrierPrice','Frais de port')->setCurrency('EUR'),
            ChoiceField::new('state')->setChoices([
                'Non payée'=> 0,
                'Payée'=> 1,
                'Préparation en cours'=> 2,
                'Livraison en cours' => 3,
            ]),
            ArrayField::new('orderDetails','Produits achetée')->hideOnIndex(),
        ];
    }
    
}

