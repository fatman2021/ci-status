<?php

namespace Piwik\Dashboard;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Piwik\Dashboard\Travis\NoTravisAccountException;
use Piwik\Dashboard\Travis\TravisClient;
use Piwik\Dashboard\User\User;
use Symfony\Component\Security\Core\SecurityContextInterface;

class RepositoryProvider
{
    const TRAVIS_ENDPOINT = 'https://api.travis-ci.org/';
    const TRAVIS_PRO_ENDPOINT = 'https://api.travis-ci.com/';

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var string[]
     */
    private $excludedRepositories;

    public function __construct(SecurityContextInterface $securityContext, $excludedRepositories)
    {
        $this->securityContext = $securityContext;
        $this->excludedRepositories = $excludedRepositories;
    }

    /**
     * Fetch the repositories the user has access to.
     *
     * @param User $user
     *
     * @return Repository[]
     */
    public function getRepositories(User $user)
    {
        $securityToken = $this->securityContext->getToken();
        if (! $securityToken instanceof OAuthToken) {
            throw new \RuntimeException('This should not happen... But just to be sure there\'s an exception');
        }
        $githubToken = $securityToken->getAccessToken();

        // Fetch from Travis.org
        $repositories = $this->fetchRepositories(self::TRAVIS_ENDPOINT, $user, $githubToken);

        // Fetch from Travis.com
        $proRepositories = $this->fetchRepositories(self::TRAVIS_PRO_ENDPOINT, $user, $githubToken);
        array_walk($proRepositories, function (Repository $repository) {
            $repository->setPro(true);
        });

        $repositories = array_merge($repositories, $proRepositories);

        $repositories = $this->removeExcludedRepositories($repositories);

        return $repositories;
    }

    /**
     * @param string $endpoint
     * @param User   $user
     * @param string $githubToken
     * @return Repository[]
     */
    private function fetchRepositories($endpoint, User $user, $githubToken)
    {
        try {
            $travis = new TravisClient($endpoint, $user, $githubToken);
            return $travis->getUserRepositories($user);
        } catch (NoTravisAccountException $e) {
            return [];
        }
    }

    /**
     * @param Repository[] $repositories
     * @return Repository[]
     */
    private function removeExcludedRepositories(array $repositories)
    {
        return array_filter($repositories, function (Repository $repository) {
            return !in_array($repository->getName(), $this->excludedRepositories);
        });
    }
}
