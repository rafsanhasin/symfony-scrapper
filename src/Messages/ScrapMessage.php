<?php


namespace App\Messages;

final class ScrapMessage
{
    private $content;
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}