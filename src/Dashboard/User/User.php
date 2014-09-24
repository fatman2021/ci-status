<?php

namespace Piwik\Dashboard\User;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $githubToken;

    /**
     * @var string|null
     */
    private $travisToken;

    public function __construct($username, $githubToken)
    {
        $this->username = (string) $username;
        $this->githubToken = $githubToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getGithubToken()
    {
        return $this->githubToken;
    }

    /**
     * @return string|null
     */
    public function getTravisToken()
    {
        return $this->travisToken;
    }

    public function setTravisToken($token)
    {
        $this->travisToken = $token;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}
