<?php

namespace Piwik\Dashboard;

use BlackBox\StorageInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;
use Piwik\Dashboard\User\User;
use Piwik\Dashboard\User\UserStorage;
use Psr\Log\LoggerInterface;

class RepositoryProvider
{
    /**
     * @var StorageInterface
     */
    private $repositoryStorage;

    /**
     * @var UserStorage
     */
    private $userStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(StorageInterface $repositoryStorage, UserStorage $userStorage, LoggerInterface $logger)
    {
        $this->repositoryStorage = $repositoryStorage;
        $this->userStorage = $userStorage;
        $this->logger = $logger;
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
        $client = $this->createApiClient($user);

        /** @var ResponseInterface $response */
        $response = $client->get('repos/?member=' . $user->getUsername());

        $repositories = array_map(function (array $repo) {
            return new Repository($repo['slug'], $repo['last_build_status']);
        }, $response->json());

        $this->repositoryStorage->set($user->getUsername(), $repositories);

        return $repositories;
    }

    private function createApiClient(User $user)
    {
        $client = new Client([
            'base_url' => 'https://api.travis-ci.org/',
        ]);

        if ($user->getTravisToken() === null) {
            $this->authenticate($client, $user);
        }
        $client->setDefaultOption('headers/Authorization', sprintf('token "%s"', $user->getTravisToken()));

        return $client;
    }

    private function authenticate(Client $client, User $user)
    {
        $this->logger->info('Getting Travis token for user {user}', ['user' => $user->getUsername()]);

        /** @var ResponseInterface $response */
        $response = $client->post('auth/github', [
            'json' => ['github_token' => $user->getGithubToken()]
        ]);
        $travisToken = $response->json()['access_token'];

        $user->setTravisToken($travisToken);
        $this->userStorage->store($user);
    }
}
