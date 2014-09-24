<?php

namespace Piwik\Dashboard;

class Repository
{
    private $name;
    private $isPro;
    private $travisToken;

    public function __construct($name, $travisToken, $isPro = false)
    {
        $this->name = (string) $name;
        $this->travisToken = (string) $travisToken;
        $this->isPro = (bool) $isPro;
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
    public function getTravisToken()
    {
        return $this->travisToken;
    }

    /**
     * @return bool
     */
    public function isPro()
    {
        return $this->isPro;
    }

    /**
     * @param bool $isPro
     */
    public function setPro($isPro)
    {
        $this->isPro = (bool) $isPro;
    }
}
