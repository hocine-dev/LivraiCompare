<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $subject = $request->request->get('subject');
            $firstName = $request->request->get('first-name');
            $lastName = $request->request->get('last-name');
            $email = $request->request->get('email');
            $message = $request->request->get('message');
            $privacy = $request->request->get('privacy');

            if ($subject && $firstName && $lastName && $email && $message && $privacy) {
                try {
                    $emailMessage = (new Email())
                        ->from($email)
                        ->to('hocinedev4@gmail.com')
                        ->subject('LivraiCompare ' . $subject)
                        ->html(
                            "<p><strong>Nom :</strong> $firstName $lastName</p>" .
                            "<p><strong>Email :</strong> $email</p>" .
                            "<p><strong>Message :</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>"
                        );

                    $mailer->send($emailMessage);

                    $this->addFlash('success', 'Votre message a été envoyé avec succès.');

                    return $this->redirectToRoute('contact');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'envoi du mail : ' . $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
            }
        }

        return $this->render('contact/contact.html.twig');
    }
}
