<?php
/**
 * Created by PhpStorm.
 * User: fabianoroberto
 * Date: 15/09/15
 * Time: 14:42
 */

namespace PaneeDesign\ApiBundle\Manager;

use Doctrine\ORM\EntityManager;

use FOS\OAuthServerBundle\Entity\TokenManager as FOSTokenManager;
use FOS\OAuthServerBundle\Model\ClientInterface;
use FOS\OAuthServerBundle\Model\Token;
use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\UserBundle\Model\UserInterface;

use OAuth2\OAuth2;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenManager extends FOSTokenManager
{
    protected $container;

    public function __construct(ContainerInterface $container, EntityManager $em, $class)
    {
        parent::__construct($em, $class);

        $this->container = $container;
    }

    /**
     * @param UserInterface $user
     * @param string $password
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function getAccessToken(UserInterface $user, $password)
    {
        $token = $this->findTokenBy(['user' => $user->getId()]);

        if ($token === null || $token->hasExpired()) {
            $accessToken = $this->getAccessTokenByCredetial($user, $password);
        } else {
            $accessToken = $token->getToken();
        }

        return $accessToken;
    }

    /**
     * @param UserInterface $user
     * @param string $refreshToken
     *
     * @return mixed|string
     * @throws \Exception
     */
    public function getOAuthToken(UserInterface $user, $refreshToken = null)
    {
        $token = $this->findTokenBy(['user' => $user->getId()]);

        if ($token && $token->hasExpired() && $refreshToken !== null) {
            $toReturn = $this->getApiAccessTokenByUser($user, OAuth2::GRANT_TYPE_REFRESH_TOKEN, $refreshToken);
        } elseif ($token && !$token->hasExpired() && $refreshToken !== null) {
            $toReturn = [
                'access_token'  => $token->getToken(),
                'refresh_token' => $refreshToken,
            ];
        } else {
            $grantType = $this->container->getParameter('ped_api.oauth.grant_url');
            $toReturn = $this->getApiAccessTokenByUser($user, $grantType);
        }

        return $toReturn;
    }

    /**
     * @param UserInterface $user
     */
    public function removeToken(UserInterface $user)
    {
        /* @var TokenInterface $token */
        $token = $this->findTokenBy(['user' => $user->getId()]);

        if ($token) {
            $refreshTokenManager = $this->container->get('fos_oauth_server.refresh_token_manager.default');
            $refreshTokenManager->deleteToken($token);
        }
    }

    /**
     * @param UserInterface $user
     * @param string $password
     *
     * @return mixed
     * @throws \Exception
     */
    private function getAccessTokenByCredetial(UserInterface $user, $password)
    {
        $url = $this->container->get('router')->generate('fos_oauth_server_token');
        $clientId = $this->container->getParameter('ped_api.client.id');
        $clientSecret = $this->container->getParameter('ped_api.client.secret');

        $request = Request::create(
            $url,
            'POST',
            [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'grant_type'    => OAuth2::GRANT_TYPE_USER_CREDENTIALS,
                'username'      => $user->getEmailCanonical(),
                'password'      => $password,
            ]
        );

        $tokenController = $this->container->get('fos_oauth_server.controller.token');

        /* @var Response $response */
        $response     = $tokenController->tokenAction($request);
        $jsonResponse = json_decode($response->getContent());

        if (property_exists($jsonResponse, 'access_token')) {
            return $jsonResponse->access_token;
        }

        throw new \Exception(
            sprintf('Unable to obtain Access Token. Response from the Server: %s ', var_export($response))
        );
    }

    /**
     * @param UserInterface $user
     * @param string $grantType
     * @param string $refreshToken
     *
     * @return mixed
     * @throws \Exception
     */
    private function getApiAccessTokenByUser(
        UserInterface $user,
        $grantType = OAuth2::GRANT_TYPE_REFRESH_TOKEN,
        $refreshToken = null
    ) {
        $url = $this->container->get('router')->generate('fos_oauth_server_token');
        $clientId     = $this->container->getParameter('ped_api.client.id');
        $clientSecret = $this->container->getParameter('ped_api.client.secret');

        if ($grantType === OAuth2::GRANT_TYPE_REFRESH_TOKEN) {
            $request = Request::create(
                $url,
                'POST',
                [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type'    => $grantType,
                    'refresh_token' => $refreshToken,
                ]
            );
        } else {
            $request = Request::create(
                $url,
                'POST',
                [
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type'    => $grantType,
                    'api_key'       => $user->getSalt(),
                ]
            );
        }

        $tokenController = $this->container->get('fos_oauth_server.controller.token');

        /* @var Response $response */
        $response = $tokenController->tokenAction($request);
        $content  = (array) json_decode($response->getContent());

        if (array_key_exists('access_token', $content)) {
            return $content;
        }

        throw new \OAuth2\OAuth2ServerException(
            $response->getStatusCode(),
            $content['error'],
            $content['error_description']
        );
    }
}
