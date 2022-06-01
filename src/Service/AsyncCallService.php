<?php 

namespace App\Service;

use App\Message\ServiceCallMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class AsyncCallService
{
    public function __construct(
        private MessageBusInterface $bus
    )
    {
        
    }

    public function async(string $className, string $methodName, array $params)
    {
        $this->bus->dispatch(new ServiceCallMessage(
            $className,
            $methodName,
            $params
        ));
    }
}