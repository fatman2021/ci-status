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
     * @var string
     */
    private $travisToken;

    /**
     * @var string
     */
    private $travisProToken;

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

    /**
     * @return string
     */
    public function getTravisToken()
    {
        return $this->travisToken;
    }

    /**
     * @param string $travisToken
     */
    public function setTravisToken($travisToken)
    {
        $this->travisToken = $travisToken;
    }

    /**
     * @return string
     */
    public function getTravisProToken()
    {
        return $this->travisProToken;
    }

    /**
     * @param string $travisProToken
     */
    public function setTravisProToken($travisProToken)
    {
        $this->travisProToken = $travisProToken;
    }
}
