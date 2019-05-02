<?php

namespace PaneeDesign\ApiBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\UserBundle\Model\UserInterface;
use PaneeDesign\ApiBundle\Manager\TokenManager;
use Symfony\Component\HttpFoundation\Request;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/**
 * @Annotations\RouteResource("Oauth")
 */
class ApiOauthController extends FOSRestController
{
    /**
     * Refresh given token
     *
     * @SWG\Tag(name="Public")
     * @SWG\Parameter(
     *     in="body",
     *     name="form",
     *     description="Access Token",
     *     @SWG\Schema(
     *         required={"access_token", "refresh_token"},
     *         @SWG\Property(
     *             type="string", property="access_token", description="The access token you got on login action"
     *         ),
     *         @SWG\Property(
     *             type="string", property="refresh_token", description="The refresh token you got on login action"
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response="200",
     *     description="Returned when successful"
     * )
     * @SWG\Response(
     *     response="503",
     *     description="Unable to refresh access token"
     * )
     *
     * @Annotations\Post("/publics/tokens/refresh")
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws \Exception
     */
    public function refreshTokenAction(Request $request)
    {
        $session = $request->getSession();
        $accessToken = $request->request->get('access_token');
        $refreshToken = $request->request->get('refresh_token');

        if ($accessToken === null) {
            $accessToken = str_replace('Bearer ', '', $request->headers->get('authorization'));
        }

        $user = $this->getMe($accessToken);

        if ($user === null) {
            throw new BadCredentialsException();
        }

        /* @var TokenManager $tokenManager */
        $tokenManager = $this->container->get('ped_api.access_token_manager.default');
        $oAuthToken = $tokenManager->getOAuthToken($user, $refreshToken);

        $session->set('access_token', $oAuthToken['access_token']);

        if (array_key_exists('refresh_token', $oAuthToken) === true) {
            $session->set('refresh_token', $oAuthToken['refresh_token']);
        }

        return $this->refreshTokenResponse($user, $oAuthToken);
    }

    /**
     * @param UserInterface $user
     * @param array         $oAuthToken
     *
     * @return array
     */
    protected function refreshTokenResponse(UserInterface $user, array $oAuthToken)
    {
        return array_merge($oAuthToken, ['id' => $user->getId()]);
    }

    /**
     * @param Request $request
     *
     * @return array|mixed|string
     */
    protected function getAccessToken(Request $request)
    {
        $accessToken = $request->request->get('access_token');
        $accessToken = trim(str_replace('Bearer', '', $accessToken));
        $headers = function_exists('getallheaders') ? getallheaders() : null;

        if ($headers !== null && isset($headers['Authorization'])) {
            $request->headers->set('Authorization', $headers['Authorization']);
        }

        if ($accessToken === null || $accessToken === '') {
            $accessToken = $request->headers->get('Authorization');
            $accessToken = trim(str_replace('Bearer', '', $accessToken));
        }

        return $accessToken;
    }

    /**
     * @param string $accessToken
     *
     * @return UserInterface
     */
    protected function getMe($accessToken)
    {
        $tokenManager = $this->container->get('fos_oauth_server.access_token_manager.default');
        $accessToken = $tokenManager->findTokenByToken($accessToken);

        if ($accessToken === null) {
            $user = null;
        } else {
            $user = $accessToken->getUser();
        }

        return $user;
    }
}
