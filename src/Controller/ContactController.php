<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Util\SendEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/nous-contacter", name="app_contact")
     */
    public function index(Request $request, SendEmail $sendEmail): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $sendEmail->send(
                'ebou.test@gmail.com',
                'La e-boutique',
                'Nouvelle demande de contact',
                sprintf("Une nouvelle demande de contact de %s %s est demandée à cette adresse : %s. Voici son message : %s", $form->getData()['prenom'], $form->getData()['nom'], $form->getData()['email'], $form->getData()['contenu'] )
            );

            $this->addFlash('notice', 'Merci de nous avoir contacté. Notre équipe va vous répondre dans les meilleurs délais.');
            return $this->redirectToRoute('app_home');

        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
