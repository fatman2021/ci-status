<?php

namespace Piwik\Dashboard\User;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserStorage
{
    public function store(User $user)
    {
        $filename = $this->getFilename($user->getUsername());

        file_put_contents($filename, serialize($user));
    }

    public function retrieve($username)
    {
        $filename = $this->getFilename($username);

        if (! file_exists($filename)) {
            $e = new UsernameNotFoundException();
            $e->setUsername($username);
            throw $e;
        }

        return unserialize(file_get_contents($filename));
    }

    private function getFilename($username)
    {
        return __DIR__ . '/../../../app/data/' . $username;
    }
}
