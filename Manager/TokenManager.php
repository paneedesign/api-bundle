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

use OAuth2\OAuth2;
use PaneeDesign\ApiBundle\Exception\JsonException;
use PaneeDesign\UserBundle\Entity\User;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenManager extends FOSTokenManager
{
    const EXPIRE_HOURS = 6;

    protected $container;

    public function __construct(ContainerInterface $container, EntityManager $em, $class)
    {
        parent::__construct($em, $class);

        $this->container = $container;
    }

    /**
     * @param User $user
     * @param $password
     * @return mixed|string
     */
    public function getAccessToken(User $user, $password)
    {
        $token = $this->findTokenBy(['user' => $user->getId()]);

        if ($token === null) {
            $client = $this->createClient(
                [
                    OAuth2::GRANT_TYPE_USER_CREDENTIALS,
                    OAuth2::GRANT_TYPE_REFRESH_TOKEN,
                    OAuth2::GRANT_TYPE_IMPLICIT,
                ]
            );

            $accessToken = $this->getAccessTokenByClient($user, $password, $client);
        } elseif ($token->hasExpired()) {
            $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
            $client        = $clientManager->findClientBy(['id' => $token->getClientId()]);

            $accessToken = $this->getAccessTokenByClient($user, $password, $client);
        } else {
            $accessToken = $token->getToken();
        }

        return $accessToken;
    }

    public function getApiKeyAccessToken(User $user, $lastAccessToken = null)
    {
        $grantApiKey = $this->container->getParameter('api_grant_url');

        if ($lastAccessToken !== null && $lastAccessToken !== '') {
            $params = [
                'user' => $user->getId(),
                'token' => $lastAccessToken,
            ];

            /* @var TokenInterface $token */
            $token = $this->findTokenBy($params);

            $expireHours = self::EXPIRE_HOURS;
            $timeLimit = new \DateTime("-$expireHours hours");

            if ($token->getExpiresAt() < $timeLimit->getTimestamp()) {
                $this->removeToken($user, $lastAccessToken);

                throw new JsonException('TokenExpired');
            } else {
                if ($token->hasExpired()) {
                    $now = new \DateTime('+1 hour');
                    $token->setExpiresAt($now->getTimestamp());

                    $refreshTokenManager = $this->container->get('fos_oauth_server.refresh_token_manager.default');
                    $refreshTokenManager->updateToken($token);
                }

                $accessToken = $token->getToken();
            }
        } else {
            $params = ['user' => $user->getId()];

            /* @var Token|TokenInterface $token */
            $token = $this->findTokenBy($params);

            if ($token === null) {
                $client = $this->createClient([$grantApiKey]);
                $accessToken = $this->getApiAccessTokenByClient($user, $client, $grantApiKey);
            } else {
                $client = $token->getClient();

                try {
                    $accessToken = $this->getApiAccessTokenByClient($user, $client, $grantApiKey);
                } catch (\Exception $e) {
                    $client = $this->createClient([$grantApiKey]);
                    $accessToken = $this->getApiAccessTokenByClient($user, $client, $grantApiKey);
                }
            }
        }

        return $accessToken;
    }

    /**
     * @param array $grantTypes
     * @return ClientInterface
     */
    public function createClient($grantTypes = [OAuth2::GRANT_TYPE_CLIENT_CREDENTIALS])
    {
        $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
        $client         = $clientManager->createClient();

        $client->setAllowedGrantTypes($grantTypes);
        $clientManager->updateClient($client);

        return $client;
    }

    /**
     * @param User $user
     * @param null|string $lastAccessToken
     */
    public function removeToken(User $user, $lastAccessToken = null)
    {
        $params = [
            'user' => $user->getId(),
        ];

        if ($lastAccessToken !== null) {
            $params['token'] = $lastAccessToken;
        }

        /* @var TokenInterface $token */
        $token = $this->findTokenBy($params);

        if ($token) {
            $refreshTokenManager = $this->container->get('fos_oauth_server.refresh_token_manager.default');
            $refreshTokenManager->deleteToken($token);
        }
    }

    /**
     * @param User $user
     * @param string $password
     * @param ClientInterface $client
     * @return mixed
     * @throws \Exception
     */
    private function getAccessTokenByClient(User $user, $password, ClientInterface $client)
    {
        $url = $this->container->get('router')->generate('fos_oauth_server_token');

        $request = Request::create(
            $url,
            'POST',
            [
                'client_id' => $client->getPublicId(),
                'client_secret' => $client->getSecret(),
                'grant_type' => OAuth2::GRANT_TYPE_USER_CREDENTIALS,
                'username' => $user->getEmailCanonical(),
                'password' => $password,
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
     * @param User $user
     * @param ClientInterface $client
     * @param string $grantType
     * @return mixed
     * @throws \Exception
     */
    private function getApiAccessTokenByClient(
        User $user,
        ClientInterface $client,
        $grantType = OAuth2::GRANT_TYPE_CLIENT_CREDENTIALS
    ) {
        $url = $this->container->get('router')->generate('fos_oauth_server_token');

        $request = Request::create(
            $url,
            'POST',
            [
                'client_id'     => $client->getPublicId(),
                'client_secret' => $client->getSecret(),
                'grant_type'    => $grantType,
                'api_key'       => $user->getSalt(),
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
}
