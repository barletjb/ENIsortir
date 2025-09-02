<?php

namespace App\Message;

class RappelMailMessage
{
    private int $sortieId;

    public function __construct(int $sortieId)
    {
        $this->sortieId = $sortieId;
    }

    public function getSortieId(): int
    {
        return $this->sortieId;
    }
}