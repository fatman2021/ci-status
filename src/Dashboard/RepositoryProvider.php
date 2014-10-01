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

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
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
        try {
            $travis = new TravisClient(self::TRAVIS_ENDPOINT, $user, $githubToken);
            $repositories = $travis->getUserRepositories($user);
        } catch (NoTravisAccountException $e) {
            $repositories = [];
        }

        // Fetch from Travis.com
        try {
            $travisPro = new TravisClient(self::TRAVIS_PRO_ENDPOINT, $user, $githubToken);
            $proRepositories = $travisPro->getUserRepositories($user);
            array_walk($proRepositories, function (Repository $repository) {
                $repository->setPro(true);
            });
        } catch (NoTravisAccountException $e) {
            $proRepositories = [];
        }

        $repositories = array_merge($repositories, $proRepositories);

        return $repositories;
    }
}
