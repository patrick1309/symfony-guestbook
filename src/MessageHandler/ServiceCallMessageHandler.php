<?php

namespace App\MessageHandler;

use App\Message\ServiceCallMessage;
use App\SpamChecker;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class ServiceCallMessageHandler implements MessageHandlerInterface, ServiceSubscriberInterface
{
    public function __construct(private ContainerInterface $container)
    {
        
    }

    public function __invoke(ServiceCallMessage $message)
    {
        $callable = [
            $this->container->get($message->getClassName()),
            $message->getMethodName()
        ];
        call_user_func_array($callable, $message->getParams());
    }

    public static function getSubscribedServices(): array
    {
        return [
            SpamChecker::class => SpamChecker::class
        ];
    }
}
