<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Util\SendEmail;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class OrderCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $updateToPreparation = Action::new('updateToPreparation', 'Préparation en cours', 'fas fa-box-open')->linkToCrudAction('updateToPreparation');
        $updateToDelivery = Action::new('updateToDelivery', 'Livraison en cours', 'fas fa-truck')->linkToCrudAction('updateToDelivery');

        return $actions
                    ->add(Crud::PAGE_INDEX, Action::DETAIL)
                    ->add('detail', $updateToPreparation)
                    ->add('detail', $updateToDelivery)
        ;
    }

    public function updateToPreparation(AdminContext $context, SendEmail $sendEmail)
    {
        $order = $context->getEntity()->getInstance();
        
        /** @var Order $order */
        $order->setState(2); // 2 = Commande en cours de préparation
        
        $this->entityManager->flush();
        
        $this->addFlash('notice', '<span style="color:green;"><strong>La commande ' . $order->getReference() . ' est bien <u>en cours de préparation</u></strong></span>');

        $sendEmail->send(
            $order->getUser()->getEmail(),
            $order->getUser()->getFullName(),
            'Votre commande est en cours de préparation',
            sprintf('Bonjour %s<br>Merci pour votre commande<br>Lorem ipsum dolor sit amet consectetur adipisicing elit. Perspiciatis cupiditate vitae modi officia deleniti sapiente at nihil quod error! Odio veniam autem enim suscipit ut. Hic repellendus doloremque rerum pariatur?', $order->getUser()->getFirstname())
        );
        
        $url = $this->adminUrlGenerator->setController(OrderCrudController::class)
                                       ->setAction(Action::INDEX)
                                       ->generateUrl()
        ;

        return $this->redirect($url);
    }

    public function updateToDelivery(AdminContext $context, SendEmail $sendEmail)
    {
        $order = $context->getEntity()->getInstance();
        
        /** @var Order $order */
        $order->setState(3); // 3 = Commande en cours de livraison
        
        $this->entityManager->flush();
        
        $this->addFlash('notice', '<span style="color:orange;"><strong>La commande ' . $order->getReference() . ' est bien <u>en cours de livraison</u></strong></span>');
        
        $sendEmail->send(
            $order->getUser()->getEmail(),
            $order->getUser()->getFullName(),
            'Votre commande est en cours de livraison',
            sprintf('Bonjour %s<br>Merci pour votre commande<br>Lorem ipsum dolor sit amet consectetur adipisicing elit. Perspiciatis cupiditate vitae modi officia deleniti sapiente at nihil quod error! Odio veniam autem enim suscipit ut. Hic repellendus doloremque rerum pariatur?', $order->getUser()->getFirstname())
        );

        $url = $this->adminUrlGenerator->setController(OrderCrudController::class)
                                       ->setAction(Action::INDEX)
                                       ->generateUrl()
        ;

        return $this->redirect($url);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id' => 'DESC']);
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateTimeField::new('createdAt', 'Date de création'),
            TextField::new('user.fullname', 'Utilisateur'),
            TextEditorField::new('delivery', 'Adresse de livraison')->onlyOnDetail(),
            ArrayField::new('orderdetails', 'Produit(s) acheté(s)')->hideOnIndex(),
            MoneyField::new('total', 'Prix total')->setCurrency('EUR'),
            TextField::new('carrierName', 'Transporteur'),
            MoneyField::new('carrierPrice', 'Frais de port')->setCurrency('EUR'),
            ChoiceField::new('state')->setChoices([
                'Commande Non payé'    => 0,
                'Commande Payée'       => 1,
                'Préparation en cours' => 2,
                'Livraison en cours'   => 3
            ])
        ];
    }
    
}
