<?php

declare(strict_types=1);

namespace PaneeDesign\ApiBundle\Controller;

use OAuth2;
use PaneeDesign\ApiBundle\OAuth2\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Route("/authorize", name="auth")
     *
     * @param Request $request
     *
     * @throws OAuth2\Exception
     *
     * @return RedirectResponse|Response
     */
    public function authAction(Request $request)
    {
        /* @var Client $client */
        $client = $this->container->get('ped_api.client.authorize_client');
        $fetchUrl = $this->container->getParameter('ped_api.oauth.authorization_url');

        if (!$request->query->get('code')) {
            return new RedirectResponse($client->getAuthenticationUrl());
        }

        $client->getAccessToken($request->query->get('code'));

        return new Response($client->fetch($fetchUrl));
    }
}
