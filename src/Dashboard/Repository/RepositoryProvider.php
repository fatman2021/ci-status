<?php

namespace Piwik\Dashboard\Repository;

use BlackBox\StorageInterface;
use Github\Client;
use Github\ResultPager;
use Piwik\Dashboard\User\User;

class RepositoryProvider
{
    /**
     * @var StorageInterface
     */
    private $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param User $user
     *
     * @return string[] Repositories
     */
    public function getRepositories(User $user)
    {
        $repositories = $this->storage->get($user->getUsername());

        if ($repositories === null) {
            $repositories = $this->syncRepositories($user);
        }

        return $repositories;
    }

    /**
     * Synchronize repositories with GitHub and stores the result.
     *
     * @param User $user
     *
     * @return string[] Synchronized repositories
     */
    public function syncRepositories(User $user)
    {
        $github = $this->createGitHubClient($user);

        $repositories = array_merge(
            $this->getUserRepositories($github, $user),
            $this->getUserOrganizationsRepositories($github, $user)
        );

        $this->storage->set($user->getUsername(), $repositories);

        return $repositories;
    }

    private function createGitHubClient(User $user)
    {
        $github = new Client();
        $github->authenticate($user->getAccessToken(), null, Client::AUTH_HTTP_TOKEN);
        return $github;
    }

    private function getUserRepositories(Client $github, User $user)
    {
        $paginator = new ResultPager($github);
        $repositories = $paginator->fetchAll($github->api('user'), 'repositories', [$user->getUsername()]);

        return array_map(function ($array) {
            return $array['full_name'];
        }, $repositories);
    }

    private function getUserOrganizationsRepositories(Client $github, User $user)
    {
        $organizations = $github->api('user')->organizations($user->getUsername());

        $repositories = [];
        foreach ($organizations as $array) {
            $organization = $array['login'];
            $repositories = array_merge($repositories, $this->getOrganizationRepositories($github, $organization));
        }

        return $repositories;
    }

    private function getOrganizationRepositories(Client $github, $organization)
    {
        $paginator = new ResultPager($github);
        $repositories = $paginator->fetchAll($github->api('organization'), 'repositories', [$organization]);

        return array_map(function ($array) {
            return $array['full_name'];
        }, $repositories);
    }
}
