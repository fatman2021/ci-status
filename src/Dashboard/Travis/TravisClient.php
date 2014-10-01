<?php

namespace Piwik\Dashboard\Travis;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\ResponseInterface;
use Piwik\Dashboard\Repository;
use Piwik\Dashboard\User\User;

/**
 * Client for the Travis CI API.
 */
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

    /**
     * @var string
     */
    private $githubToken;

    /**
     * @param string $apiEndpoint You need to provide the API endpoint because it can be Travis.org or Travis.com
     * @param User   $user
     * @param string $githubToken
     */
    public function __construct($apiEndpoint, User $user, $githubToken)
    {
        $this->apiEndpoint = (string) $apiEndpoint;
        $this->user = $user;
        if (! $githubToken) {
            throw new \InvalidArgumentException('The GitHub token provided is empty');
        }
        $this->githubToken = (string) $githubToken;

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
        try {
            /** @var ResponseInterface $response */
            $response = $this->client->post('auth/github', [
                'json' => ['github_token' => $this->githubToken]
            ]);
        } catch (RequestException $e) {
            $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;

            // Right now Travis returns a 500 error if the token is invalid so it's impossible to test (sigh...)
            if ($status === 401 || $status === 403 || $status === 500) {
                throw new NoTravisAccountException;
            }

            throw $e;
        }

        $this->travisToken = $response->json()['access_token'];
    }
}
