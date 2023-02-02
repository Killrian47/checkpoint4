<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailSender extends AbstractController
{
    public MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function emailForLooser(int $numberOfChoco, string $userEmail): void
    {
        $mail = (new Email())
            ->from($this->getParameter('mailer_from'))
            ->to($userEmail)
            ->subject('You got selected by the draw to pay the choco !')
            ->html($this->renderView('mail/emailForLooser.html.twig', [
                'choco' => $numberOfChoco,
                'userEmail' => $userEmail
            ]));
        $this->mailer->send($mail);
    }
}