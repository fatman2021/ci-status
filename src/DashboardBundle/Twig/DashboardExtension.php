<?php

namespace Piwik\DashboardBundle\Twig;

use Piwik\Dashboard\Repository;

class DashboardExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('githubUrl', [$this, 'githubUrl'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('travisUrl', [$this, 'travisUrl'], ['is_safe' => ['html']]),
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

    public function getName()
    {
        return 'dashboard';
    }
}
