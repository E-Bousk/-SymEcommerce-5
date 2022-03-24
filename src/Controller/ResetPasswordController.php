<?php

namespace App\Controller;

use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Util\SendEmail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/mot-de-passe-oublie", name="app_reset_password")
     */
    public function index(Request $request, SendEmail $sendEmail): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->get('email')) {
            /** @var User $user */
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));

            if ($user) {
                // 1 : enregistre en base la demande de réinitialisation du mot de passe
                $resetPassword = (new ResetPassword())->setUser($user)
                                                      ->setToken(md5(uniqid()))
                                                      ->setCreatedAt(new \DateTime())
                ;
                $this->entityManager->persist($resetPassword);
                $this->entityManager->flush();

                // 2 : envoie d'e-mail avec lien à l'utilisateur
                $url = $this->generateUrl('app_update_password', ['token' => $resetPassword->getToken()]);
                $content  = "<p>Vous avez demandé à réinitialiser votre mot de passe sur le site La e-boutique</p>";
                $content .= "<p>Merci de bien vouloir cliquer sur le lien suivant pour <a href=\"$url\">mettre à jour votre mot de passe</a><p>";
                $content .= "<p>Si vous n'êtes pas à l'origine de cette demande, merci de ne pas tenir compte de ce message<p>";
                $sendEmail->send(
                    $user->getEmail(),
                    $user->getFullName(),
                    'Réinitialisation du mot de passe sur la e-boutique',
                    sprintf('Bonjour %s, %s', $user->getFirstname(), $content)
                );

                $this->addFlash('notice', 'Un e-mail avec un lien pour réinitialiser votre mot de passe vous a été envoyé à l\'adresse indiquée.');
            } else {
                // ‼ FAKE MESSAGE ‼ (pour ne pas dire que l'adresse e-mail est inconne)
                $this->addFlash('notice', 'Un e-mail avec la procédure pour réinitialiser votre mot de passe vous a été envoyé à l\'adresse indiquée.');
            }
            
        }

        return $this->render('reset_password/index.html.twig');
    }

    /**
     * @Route("/modifier-mot-de-passe/{token}", name="app_update_password")
     */
    public function updatePassword($token, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $resetPassword = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);
        
        if (!$resetPassword) {
            return $this->redirectToRoute('app_reset_password');
        }
        
        /** @var ResetPassword $resetPassword */
        if (new \Datetime() > $resetPassword->getCreatedAt()->modify('+ 3 hour')) {
            $this->addFlash('notice', 'Votre demande de réinitialisation de mot de passe a expiré. Merci de la renouveller.');
            return $this->redirectToRoute('app_reset_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('new_password')->getData();

            $resetPassword->getUser()->setPassword($encoder->encodePassword($resetPassword->getUser(), $newPassword));

            $this->entityManager->flush();
            
            $this->addFlash('notice', 'Votre mot de passe a bien été réinitialisé.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
