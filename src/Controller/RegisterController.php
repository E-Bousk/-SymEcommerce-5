<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Util\SendEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * @Route("/inscription", name="app_register")
     */
    public function index(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        SendEmail $sendEmail
    ): Response
    {
        $notification = null;

        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $searchEmail = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if (!$searchEmail) {
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));
            
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $sendEmail->send(
                    $user->getEmail(),
                    $user->getFullName(),
                    'Bienvenue sur la e-boutique',
                    sprintf('Bonjour %s<br>Bienvenue sur la e-boutique<br>Lorem ipsum dolor sit amet consectetur adipisicing elit. Perspiciatis cupiditate vitae modi officia deleniti sapiente at nihil quod error! Odio veniam autem enim suscipit ut. Hic repellendus doloremque rerum pariatur?', $user->getFirstname())
                );

                $notification = "Votre inscription s'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte.";
            } else {
                $notification = "L'e-mail que vous avez renseigné existe déjà.";
            }
        }

        return $this->render('register/index.html.twig', [
            'form'         => $form->createView(),
            'notification' => $notification
        ]);
    }
}
