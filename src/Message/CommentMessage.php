<?php

namespace App\Message;

final class CommentMessage
{
    /*
     * Add whatever properties and methods you need
     * to hold the data for this message class.
     */

    private $id;
    private $reviewUrl;
    private $context;

    public function __construct(string $id, string $reviewUrl, array $context = [])
    {
        $this->id = $id;
        $this->reviewUrl = $reviewUrl;
        $this->context = $context;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of context
     */
    public function getContext()
    {
        return $this->context;
    }

    public function getReviewUrl(): string
    {
        return $this->reviewUrl;
    }
}
