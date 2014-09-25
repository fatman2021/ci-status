<?php

namespace Piwik\Dashboard;

use BlackBox\StorageInterface;
use Piwik\Dashboard\Travis\TravisClient;
use Piwik\Dashboard\User\User;

class RepositoryProvider
{
    const TRAVIS_ENDPOINT = 'https://api.travis-ci.org/';
    const TRAVIS_PRO_ENDPOINT = 'https://api.travis-ci.com/';

    /**
     * @var StorageInterface
     */
    private $repositoryStorage;

    public function __construct(StorageInterface $repositoryStorage)
    {
        $this->repositoryStorage = $repositoryStorage;
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
        $repositories = $this->repositoryStorage->get($user->getUsername());

        if ($repositories === null) {
            $repositories = $this->syncRepositories($user);
        }

        return $repositories;
    }

    /**
     * Synchronize repositories with Travis and stores the result.
     *
     * @param User $user
     *
     * @return string[] Synchronized repositories
     */
    public function syncRepositories(User $user)
    {
        // Fetch from Travis.org and Travis.com
        $travis = new TravisClient(self::TRAVIS_ENDPOINT, $user);
        $repositories = $travis->getUserRepositories($user);

        // Fetch from Travis.com
        $travisPro = new TravisClient(self::TRAVIS_PRO_ENDPOINT, $user);
        $proRepositories = $travisPro->getUserRepositories($user);
        array_walk($proRepositories, function (Repository $repository) {
            $repository->setPro(true);
        });

        $repositories = array_merge($repositories, $proRepositories);

        $this->repositoryStorage->set($user->getUsername(), $repositories);

        return $repositories;
    }
}
