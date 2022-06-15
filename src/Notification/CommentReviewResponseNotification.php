<?php

namespace App\Notification;

use App\Entity\Comment;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;

class CommentReviewResponseNotification extends Notification implements EmailNotificationInterface
{
    public function __construct(private Comment $comment)
    {
        parent::__construct('Your comment moderation');
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): ?EmailMessage
    {
        $message = EmailMessage::fromNotification($this, $recipient, $transport);
        $message->getMessage()
            ->htmlTemplate('emails/comment_review_response_notification.html.twig')
            ->context(['comment' => $this->comment])
        ;

        return $message;
    }
}
