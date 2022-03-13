<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccountPasswordController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/compte/modifier-mot-de-passe", name="app_account_edit_password")
     */
    public function index(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $notification = null;

        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $oldPassword = $request->request->get('change_password')['old_password'];
            $oldPassword = $form->get('old_password')->getData();

            if ($encoder->isPasswordValid($user, $oldPassword)) {
                $newPassword = $form->get('new_password')->getData();

                $user->setPassword($encoder->encodePassword($user, $newPassword));

                $this->entityManager->flush();

                $notification = 'Votre mot de passe a bien été mis à jour.';
            } else {
                $notification = 'Votre mot de passe actuel n\'est pas le bon.';
            }
        }

        return $this->render('account/password.html.twig', [
            'form'         => $form->createView(),
            'notification' => $notification
        ]);
    }
}
