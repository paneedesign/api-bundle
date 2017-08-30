<?php

namespace PaneeDesign\ApiBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;

use PaneeDesign\ApiBundle\Exception\JsonException;
use PaneeDesign\ApiBundle\Helper\ApiHelper;
use PaneeDesign\ApiBundle\Manager\TokenManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use /** @noinspection PhpUnusedAliasInspection */ FOS\RestBundle\Controller\Annotations;
use /** @noinspection PhpUnusedAliasInspection */ Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
     * @ApiDoc(
     *   section = "Public",
     *   resource = true,
     *   requirements = {
     *      {
     *          "name" = "access_token",
     *          "dataType" = "string",
     *          "requirement" = "[a-zA-Z0-9]+",
     *          "description" = "OAuth2 Access Token to allow call"
     *      }
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   },
     *   views = { "public", "default" }
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
        $expireHours = 6;

        try {
            if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                /* @var TokenInterface $token */
                $token = $this->get('security.token_storage')->getToken();
                $user  = $token->getUser();

                if ($user == null) {
                    throw new AuthenticationException();
                }

                /* @var TokenManager $tokenManager */
                $tokenManager = $this->container->get('ped_api.access_token_manager.default');
                $accessToken  = $tokenManager->getApiKeyAccessToken($user, $accessToken, $expireHours);

                $session->set('access_token', $accessToken);

                $toReturn = ApiHelper::successResponse([
                    'access_token' => $accessToken,
                    'id'           => $user->getId()
                ]);
            } else {
                throw new AuthenticationException();
            }
        } catch (JsonException $jsonException) {
            $message = $this->translate(
                'api.token.expired_hours_exception',
                ['%expirePeriod%' => $expireHours],
                null,
                $this->locale
            );

            $toReturn = ApiHelper::customResponse(
                Response::HTTP_UNAUTHORIZED,
                1401,
                'token_expired',
                $message
            );
        } catch (\Exception $exception) {
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
        }

        return $toReturn;
    }

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
}
