<?php

namespace App\MessageHandler;

use App\SpamChecker;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

final class CommentMessageHandler implements MessageHandlerInterface
{
    private $workflow;

    public function __construct(
        private EntityManagerInterface $em,
        private SpamChecker $spamChecker,
        private CommentRepository $commentRepository,
        private MessageBusInterface $bus,        
        private WorkflowInterface $commentStateMachine,
        private MailerInterface $mailer,
        private string $adminEmail,
        private ?LoggerInterface $logger = null
    )
    {
        $this->workflow = $commentStateMachine;
    }

    public function __invoke(CommentMessage $message)
    {
        // recup du commentaire
        $comment = $this->commentRepository->findOneBy(['id' => $message->getId()]);
        if (!$comment) {
            return;
        }

        // verif du score de spam via Akismet Api
        if ($this->workflow->can($comment, 'accept')) {
            $score = $this->spamChecker->getSpamScore($comment, $message->getContext());
            switch ($score) {
                case 2: $transition = 'reject_spam'; break;
                case 1: $transition = 'might_be_spam'; break;
                default: $transition = 'accept';
            }

            $this->workflow->apply($comment, $transition);
            $this->em->flush();

            $this->bus->dispatch($message);
        } elseif ($this->workflow->can($comment, 'publish') || $this->workflow->can($comment, 'publish_ham')) {
            $this->mailer->send((new NotificationEmail())
                ->subject('New comment posted')
                ->htmlTemplate('emails/comment_notification.html.twig')
                ->from($this->adminEmail)
                ->to($this->adminEmail)
                ->context(['comment' => $comment])
            );
        }
        else {
            $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }
    }
}
