<?php
declare(strict_types=1);

namespace BrenoRoosevelt\OAuth2\Client\Example;

/**
 * Classe auxiliar para armazenar o "state" na sessão
 */
class StateStorage
{
    public function __construct()
    {
        @session_start();
    }

    public function store($v): void
    {
        $_SESSION['oauth'] = $v;
    }

    public function has($v): bool
    {
        return isset($_SESSION['oauth']) && $_SESSION['oauth'] === $v;
    }

    public function clear(): void
    {
        unset($_SESSION['oauth']);
    }
}
