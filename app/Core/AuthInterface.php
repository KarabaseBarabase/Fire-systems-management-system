<?php
namespace App\Core;

interface AuthInterface
{
    public function login($user): bool;
    public function logout(): bool;
    public function check(): bool;
    public function user();
    public function getUserId(): ?int;
    public function getLoginTime(): ?int;
    public function getSessionId(): string;
    public function updateLastActivity(): void;
}