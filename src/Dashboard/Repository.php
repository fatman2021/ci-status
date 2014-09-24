<?php

namespace Piwik\Dashboard;

class Repository
{
    const BUILD_SUCCESS = 0;
    const BUILD_FAILURE = 1;
    const BUILD_ERROR   = 2;
    const BUILD_UNKNOWN = 3;

    private $name;
    private $buildStatus;

    public function __construct($name, $buildStatus)
    {
        $this->name = (string) $name;
        $this->buildStatus = (string) $buildStatus;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getBuildStatus()
    {
        return $this->buildStatus;
    }

    /**
     * @return string
     */
    public function getTravisUrl()
    {
        return 'https://travis-ci.org/' . $this->name;
    }

    /**
     * @return string
     */
    public function getGithubUrl()
    {
        return 'https://github.com/' . $this->name;
    }
}
