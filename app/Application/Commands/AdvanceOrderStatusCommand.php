<?php

namespace App\Application\Commands;

readonly class AdvanceOrderStatusCommand
{
    public function __construct(
        public int $orderId
    ) {}
} 