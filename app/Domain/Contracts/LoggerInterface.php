<?php

namespace App\Domain\Contracts;

interface LoggerInterface
{
    public function info(string $message, array $context = []): void;
    
    public function error(string $message, array $context = []): void;
    
    public function warning(string $message, array $context = []): void;
    
    public function debug(string $message, array $context = []): void;
} 