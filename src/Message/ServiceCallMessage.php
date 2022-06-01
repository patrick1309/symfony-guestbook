<?php

namespace App\Message;

final class ServiceCallMessage
{
    /*
     * Add whatever properties and methods you need
     * to hold the data for this message class.
     */

    private $className;
    private $methodName;
    private $params;

    public function __construct(string $className, string $methodName, array $params)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->params = $params;
    }

    /**
     * Get the value of className
     */ 
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Get the value of methodName
     */ 
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * Get the value of params
     */ 
    public function getParams()
    {
        return $this->params;
    }
}
