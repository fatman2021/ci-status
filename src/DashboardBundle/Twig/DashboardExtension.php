<?php

namespace Piwik\DashboardBundle\Twig;

use Piwik\Dashboard\Repository;

class DashboardExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('badge', [$this, 'renderBadge'], ['is_safe' => ['html']]),
        ];
    }

    public function renderBadge($status)
    {
        switch ($status) {
            case Repository::BUILD_SUCCESS:
                $text = 'Build success';
                $class = 'success';
                break;
            case Repository::BUILD_FAILURE:
                $text = 'Build failure';
                $class = 'danger';
                break;
            case Repository::BUILD_ERROR:
                $text = 'Build error';
                $class = 'danger';
                break;
            case Repository::BUILD_UNKNOWN:
            default:
                $text = 'Build unknown';
                $class = 'warning';
                break;
        }

        return sprintf('<span class="label label-%s">%s</span>', $class, $text);
    }

    public function getName()
    {
        return 'dashboard';
    }
}
