<?php

namespace Piwik\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller
{
    public function showAction()
    {
        $repositoryProvider = $this->get('dashboard.repository_provider');

        try {
            $repositories = $repositoryProvider->getRepositories($this->getUser());
        } catch (\Exception $e) {
            $repositories = [];

            $this->get('session')->getFlashBag()->add(
                'danger',
                'There was an error while retrieving the repositories: ' . $e->getMessage()
            );
        }

        return $this->render('@Dashboard/Dashboard/dashboard.html.twig', [
            'repositories' => $repositories,
        ]);
    }
}
