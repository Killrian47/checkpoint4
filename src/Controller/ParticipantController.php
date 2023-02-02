<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Repository\UserRepository;
use App\Service\EmailSender;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Config\Doctrine\Orm\EntityManagerConfig;

#[Route('/participant')]
class ParticipantController extends AbstractController
{
    #[Route('/', name: 'app_participant_index', methods: ['GET'])]
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_participant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ParticipantRepository $participantRepository): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participantRepository->save($participant, true);

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant/new.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/join', name: 'app_join_participant')]
    public function joinParticipants(EntityManagerInterface $manager): Response
    {
        $participant = new Participant();
        $user = $this->getUser();
        $participant->setEmail($user->getEmail());
        $participant->setFirstname($user->getFirstname());
        $participant->setFirstname($user->getLastname());
        $manager->persist($participant);

        $manager->flush();
        return $this->redirectToRoute('app_participant_index');
    }

    /**
     * @throws Exception
     */
    #[Route('/reset', name: 'app_reset_participant')]
    public function resetParticipant(EntityManagerInterface $entityManager): Response
    {
        $connection = $entityManager->getConnection();
        $platform   = $connection->getDatabasePlatform();

        $connection->executeUpdate($platform->getTruncateTableSQL('participant', true /* whether to cascade */));
        return $this->redirectToRoute('app_participant_index');

    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/start', name: 'app_start_random')]
    public function startGetLooser(
        ParticipantRepository $participantRepository,
        UserRepository $userRepository,
        EmailSender $emailSender): Response
    {
        $totalParticipant = 0;
        $numberOfParticipants = $participantRepository->countParticipants();
        foreach ($numberOfParticipants as $participants) {
            foreach ($participants as $participant) {
                $totalParticipant = $participant;
            }
        }
        $looser = rand(1, $totalParticipant);
        $userLooser = $userRepository->find($looser);
        $emailSender->emailForLooser($totalParticipant, $userLooser->getEmail());

        return $this->render('participant/looser.html.twig', [
            'theLooser' => $userLooser,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_show', methods: ['GET'])]
    public function show(Participant $participant): Response
    {
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participant $participant, ParticipantRepository $participantRepository): Response
    {
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participantRepository->save($participant, true);

            return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participant_delete', methods: ['POST'])]
    public function delete(Request $request, Participant $participant, ParticipantRepository $participantRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->request->get('_token'))) {
            $participantRepository->remove($participant, true);
        }

        return $this->redirectToRoute('app_participant_index', [], Response::HTTP_SEE_OTHER);
    }


}
