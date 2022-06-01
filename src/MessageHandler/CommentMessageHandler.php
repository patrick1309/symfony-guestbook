<?php

namespace App\MessageHandler;

use App\SpamChecker;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class CommentMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private SpamChecker $spamChecker,
        private CommentRepository $commentRepository
    )
    {
        
    }

    public function __invoke(CommentMessage $message)
    {
        // recup du commentaire
        $comment = $this->commentRepository->findOneBy(['id' => $message->getId()]);
        if (!$comment) {
            return;
        }

        // verif du score de spam via Akismet Api
        if (2 === $this->spamChecker->getSpamScore($comment, $message->getContext())) {
            $comment->setState('spam');
        }
        else {
            $comment->setState('published');
        }

        // maj de l'état du commentaire en bdd
        $this->em->flush();
    }
}
