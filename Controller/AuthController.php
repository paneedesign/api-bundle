<?php

namespace PaneeDesign\ApiBundle\Controller;

use PaneeDesign\ApiBundle\OAuth2\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use OAuth2;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
     * @return RedirectResponse|Response
     * @throws OAuth2\Exception
     */
    public function authAction(Request $request)
    {
        /* @var Client $client */
        $client   = $this->container->get('ped_api.client.authorize_client');
        $fetchUrl = $this->container->getParameter('ped_api.oauth.authorization_url');

        if (!$request->query->get('code')) {
            return new RedirectResponse($client->getAuthenticationUrl());
        }

        $client->getAccessToken($request->query->get('code'));

        return new Response($client->fetch($fetchUrl));
    }
}
