<?php

namespace PaneeDesign\ApiBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;

use PaneeDesign\ApiBundle\Exception\JsonException;
use PaneeDesign\ApiBundle\Helper\ApiHelper;
use PaneeDesign\ApiBundle\Manager\TokenManager;
use PaneeDesign\UserBundle\Entity\User;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Swagger\Annotations as SWG;

/**
 * @Annotations\RouteResource("Public")
 */
class ApiPublicController extends FOSRestController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    protected $locale;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $request = Request::createFromGlobals();
        $locale  = $request->query->get('_locale');

        if ($locale === null) {
            $locale = $request->headers->get('_locale');
        }

        $this->locale = $locale;
    }

    /**
     * Refresh given token
     *
     * @SWG\Tag(name="Public")
     * @SWG\Parameter(
     *     in="body",
     *     name="form",
     *     description="Access Token",
     *     @SWG\Schema(
     *         required={"access_token"},
     *         @SWG\Property(
     *             type="string", property="access_token", description="The token you got on login action"
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
     * @return Response
     */
    public function refreshTokenAction(Request $request)
    {
        $session     = $request->getSession();
        $accessToken = $this->getAccessToken($request);
        $toReturn    = null;
        $expiredAt   = $this->container->getParameter('ped_api.access_token.expire_at');

        try {
            $user = $this->getMe($accessToken);

            if ($user == null) {
                throw new AuthenticationException();
            }

            /* @var TokenManager $tokenManager */
            $tokenManager = $this->container->get('ped_api.access_token_manager.default');
            $accessToken  = $tokenManager->getApiKeyAccessToken($user, $accessToken);

            $session->set('access_token', $accessToken);

            $toReturn = $this->refreshTokenResponse($user, $accessToken);
        } catch (JsonException $jsonException) {
            $toReturn = $this->throwRefreshTokenJsonException($jsonException, $expiredAt);
        } catch (\Exception $exception) {
            $toReturn = $this->throwRefreshTokenException($exception);
        }

        return $toReturn;
    }

    /**
     * @param User $user
     * @param $accessToken
     *
     * @return array
     */
    protected function refreshTokenResponse(User $user, $accessToken)
    {
        return ApiHelper::successResponse([
            'access_token' => $accessToken,
            'id'           => $user->getId()
        ]);
    }

    /**
     * @param JsonException $jsonException
     * @param $expiredAt
     * @return Response
     */
    protected function throwRefreshTokenJsonException(JsonException $jsonException, $expiredAt)
    {
        $message = $this->translate(
            'api.token.expired_exception',
            ['%expirePeriod%' => $expiredAt],
            null,
            $this->locale
        );

        $toReturn = ApiHelper::customResponse(
            Response::HTTP_UNAUTHORIZED,
            1401,
            'token_expired',
            $message
        );

        return $toReturn;
    }

    /**
     * @param \Exception $exception
     * @return Response
     */
    protected function throwRefreshTokenException(\Exception $exception)
    {
        $message = $this->translate(
            'api.token.refresh_exception',
            ['%exception%' => $exception->getMessage()],
            null,
            $this->locale
        );

        $toReturn = ApiHelper::customResponse(
            Response::HTTP_SERVICE_UNAVAILABLE,
            1503,
            'token_exception',
            $message
        );

        return $toReturn;
    }

    /**
     * @param Request $request
     * @return array|mixed|string
     */
    protected function getAccessToken(Request $request)
    {
        $accessToken = $request->request->get('access_token');
        $accessToken = trim(str_replace('Bearer', '', $accessToken));
        $headers     = function_exists('getallheaders') ? getallheaders() : null;

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
     * Translates the given message.
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return string The translated string
     */
    protected function translate($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $translator = $this->get('translator');

        return $translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * @param string $accessToken
     *
     * @return User
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
