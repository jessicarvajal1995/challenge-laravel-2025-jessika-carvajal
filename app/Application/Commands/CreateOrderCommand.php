<?php

namespace App\Application\Commands;

readonly class CreateOrderCommand
{
    public function __construct(
        public string $clientName,
        public array $items
    ) {}
} 