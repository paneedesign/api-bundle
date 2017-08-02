<?php

namespace PaneeDesign\ApiBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;

use PaneeDesign\ApiBundle\Exception\JsonException;
use PaneeDesign\ApiBundle\Helper\ApiHelper;
use PaneeDesign\ApiBundle\Manager\TokenManager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

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
     * @return array|JsonResponse
     * @throws JsonException
     */
    public function refreshTokenAction(Request $request)
    {
        $session     = $request->getSession();
        $accessToken = $this->getAccessToken($request);

        try {
            if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                $user = $this->get('security.token_storage')
                    ->getToken()->getUser();

                if ($user == null) {
                    throw new AuthenticationException();
                }

                /* @var TokenManager $tokenManager */
                $tokenManager = $this->container->get('ped_api.access_token_manager.default');
                $accessToken  = $tokenManager->getApiKeyAccessToken($user, $accessToken);

                $session->set('access_token', $accessToken);

                $toReturn = ApiHelper::successResponse([
                    'access_token' => $accessToken,
                    'id'           => $user->getId()
                ]);
            } else {
                throw new AuthenticationException();
            }
        } catch (\Exception $e) {
            $message  = $this->translate('api.shared.error_refresh_token', array(), null, $this->locale);
            throw new JsonException($message, Response::HTTP_UNAUTHORIZED);
        }

        return $toReturn;
    }

    protected function getAccessToken(Request $request)
    {
        $accessToken = $request->query->get('access_token');
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
