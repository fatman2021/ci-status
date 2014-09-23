<?php

namespace Piwik\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller
{
    public function showAction()
    {
        $repositoryProvider = $this->get('dashboard.repository_provider');

        $repositories = $repositoryProvider->getRepositories($this->getUser());

        return $this->render('@Dashboard/Dashboard/dashboard.html.twig', [
            'repositories' => $repositories,
        ]);
    }
}
