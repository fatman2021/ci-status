<?php

namespace Piwik\DashboardBundle\Twig;

use Piwik\Dashboard\Repository;

class DashboardExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('githubUrl', [$this, 'githubUrl']),
            new \Twig_SimpleFunction('travisUrl', [$this, 'travisUrl']),
            new \Twig_SimpleFunction('prettyRepositoryName', [$this, 'prettyRepositoryName'], ['is_safe' => ['html']]),
        ];
    }

    public function githubUrl(Repository $repository)
    {
        return 'https://github.com/' . $repository->getName();
    }

    public function travisUrl(Repository $repository)
    {
        if ($repository->isPro()) {
            return 'https://magnum.travis-ci.com/' . $repository->getName();
        }

        return 'https://travis-ci.org/' . $repository->getName();
    }

    public function prettyRepositoryName(Repository $repository)
    {
        list($account, $name) = explode('/', $repository->getName(), 2);

        return sprintf('<span class="repo-account">%s</span><span class="repo-separator">/</span>%s', $account, $name);
    }

    public function getName()
    {
        return 'dashboard';
    }
}
