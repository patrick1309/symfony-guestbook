<?php

namespace App\MessageHandler;

use App\SpamChecker;
use App\ImageOptimizer;
use Psr\Log\LoggerInterface;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use App\Notification\CommentReviewNotification;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Notification\CommentReviewResponseNotification;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

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
        private ImageOptimizer $imageOptimizer,
        private string $photoDir,
        private NotifierInterface $notifier,
        private ?LoggerInterface $logger = null
    ) {
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
                case 2:
                    $transition = 'reject_spam';
                    break;
                case 1:
                    $transition = 'might_be_spam';
                    break;
                default:
                    $transition = 'accept';
            }

            $this->workflow->apply($comment, $transition);
            $this->em->flush();

            $this->bus->dispatch($message);
        } elseif ($this->workflow->can($comment, 'publish') || $this->workflow->can($comment, 'publish_ham')) {
            $this->notifier->send(new CommentReviewNotification($comment, $message->getReviewUrl()), ...$this->notifier->getAdminRecipients());
        } elseif ($this->workflow->can($comment, 'optimize')) {
            if ($comment->getPhotoFilename()) {
                $this->imageOptimizer->resize($this->photoDir . '/' . $comment->getPhotoFilename());
            }
            $this->workflow->apply($comment, 'optimize');
            $this->em->flush();
            $this->notifier->send(new CommentReviewResponseNotification($comment), new Recipient($comment->getEmail()));
        } else {
            $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }
    }
}
