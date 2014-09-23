<?php

namespace Piwik\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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

    public function syncAction()
    {
        $repositoryProvider = $this->get('dashboard.repository_provider');

        $repositoryProvider->syncRepositories($this->getUser());

        $this->get('session')->getFlashBag()->add(
            'success',
            'The repositories have been synchronized with GitHub'
        );

        return new Response('', 204);
    }
}
