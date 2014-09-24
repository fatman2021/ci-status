<?php

namespace Piwik\Dashboard\Travis;

use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;
use Piwik\Dashboard\Repository;
use Piwik\Dashboard\User\User;

class TravisClient
{
    /**
     * @var string
     */
    private $apiEndpoint;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $travisToken;

    public function __construct($apiEndpoint, User $user)
    {
        $this->apiEndpoint = (string) $apiEndpoint;
        $this->user = $user;

        $this->createApiClient();
    }

    /**
     * Fetch all the repositories the user can see on Travis.
     *
     * @return Repository[]
     */
    public function getUserRepositories()
    {
        /** @var ResponseInterface $response */
        $response = $this->client->get('repos/?member=' . $this->user->getUsername());

        $repositories = $response->json()['repos'];

        return array_map(function (array $repo) {
            return new Repository($repo['slug'], $this->travisToken);
        }, $repositories);
    }

    private function createApiClient()
    {
        $this->client = new Client([
            'base_url' => $this->apiEndpoint,
            'defaults' => [
                'headers' => [
                    'Accept' => 'application/vnd.travis-ci.2+json',
                ],
            ],
        ]);

        $this->authenticate();
        $this->client->setDefaultOption('headers/Authorization', sprintf('token "%s"', $this->travisToken));
    }

    /**
     * Authenticate to Travis using a GitHub token to get a Travis token.
     */
    private function authenticate()
    {
        /** @var ResponseInterface $response */
        $response = $this->client->post('auth/github', [
            'json' => ['github_token' => $this->user->getGithubToken()]
        ]);

        $this->travisToken = $response->json()['access_token'];
    }
}
