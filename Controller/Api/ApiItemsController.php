<?php

namespace PaneeDesign\ApiBundle\Controller\Api;

use PaneeDesign\ApiBundle\Exception\InvalidFormException;
use PaneeDesign\ApiBundle\Handler\ItemHandler;

use PaneeDesign\ApiBundle\Helper\ApiHelper;
use PaneeDesign\ApiBundle\Manager\TokenManager;

use PaneeDesign\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;

use /** @noinspection PhpUnusedAliasInspection */ FOS\RestBundle\Controller\Annotations;
use /** @noinspection PhpUnusedAliasInspection */ Nelmio\ApiDocBundle\Annotation\ApiDoc;

abstract class ApiItemsController extends FOSRestController
{
    /**
     * @var ItemHandler
     */
    private $handler;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $request = Request::createFromGlobals();
        $locale  = $request->query->get('_locale');

        $accessToken = $this->getAccessToken($request);

        if ($locale === null) {
            $locale = $request->headers->get('_locale');
        }

        $this->handler = $this->getHandler();
        $this->handler->setAccessToken($accessToken);
        $this->handler->setLocale($locale);
    }

    /**
     * @return ItemHandler
     */
    abstract protected function getHandler();

    /**
     * List all items.
     *
     * @Operation(
     *     tags={""},
     *     summary="List all items.",
     *     @SWG\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Offset from which to start listing items.",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         description="How many items to return.",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     *
     * @Annotations\QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     strict=true,
     *     default=0,
     *     description="Offset from which to start listing items."
     * )
     * @Annotations\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     strict=true,
     *     default=5,
     *     description="How many items to return.")
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     *
     * @return array
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        $offset = $paramFetcher->get('offset');
        $limit  = $paramFetcher->get('limit');

        return $this->handler->all($limit, $offset);
    }


    /**
     * Create a Item from the submitted data.
     *
     * @ApiDoc(
     *   resource = true,
     *   requirements = {
     *      {
     *          "name" = "access_token",
     *          "dataType" = "string",
     *          "requirement" = "[a-zA-Z0-9]+",
     *          "description" = "OAuth2 Access Token to allow call"
     *      }
     *   },
     *   description = "Creates a new item from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   views = { "items", "default" }
     * )
     *
     * @param Request $request the request object
     *
     * @return object
     * @throws \Exception
     */
    public function postAction(Request $request)
    {
        try {
            $toPost  = ApiHelper::formatRequestData($request->request->all());
            $newItem = $this->handler->post(
                $toPost
            );

            return $newItem;
        } catch (InvalidFormException $exception) {
            return $exception->getForm();
        }
    }

    /**
     * Partially update an Item from the submitted data
     *
     * @Operation(
     *     tags={""},
     *     summary="Partially update an Item from the submitted data",
     *     @SWG\Response(
     *         response="204",
     *         description="Returned when successful"
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when the form has errors"
     *     )
     * )
     *
     *
     * @param Request $request the request object
     * @param int $id the item id
     *
     * @return object
     *
     * @throws NotFoundHttpException when item not exist
     * @throws \Exception
     */
    public function patchAction(Request $request, $id)
    {
        $toPatch = ApiHelper::formatRequestData($request->request->all());
        $newItem = $this->getHandler()->patch(
            $id,
            $toPatch
        );

        return $newItem;
    }

    /**
     * Delete Item from the submitted data
     *
     * @ApiDoc(
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
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   },
     *   views = { "items", "default" }
     * )
     *
     * @param int $id the item id
     *
     * @return boolean
     */
    public function deleteAction($id)
    {
        return $this->getHandler()->delete($id);
    }

    /**
     * Get single Item.
     *
     * @Operation(
     *     tags={""},
     *     summary="Gets a Item for a given id",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     ),
     *     @SWG\Response(
     *         response="403",
     *         description="Returned when the item is not authorized to say hello"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when the item is not found"
     *     )
     * )
     *
     *
     * @Annotations\View(templateVar="item")
     *
     * @param ParamFetcherInterface $paramFetcher
     * @param int $id the item id
     *
     * @return object
     *
     * @throws NotFoundHttpException when item not exist
     */
    public function getAction(ParamFetcherInterface $paramFetcher, $id)
    {
        $item = $this->getOr404($id);

        return $item;
    }

    /**
     * Get number of items.
     *
     * @Operation(
     *     tags={""},
     *     summary="Get number of items.",
     *     @SWG\Parameter(
     *         name="filters",
     *         in="query",
     *         description="Custom filter object",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when the form has errors"
     *     )
     * )
     *
     *
     * @Annotations\QueryParam(name="filters", description="Custom filter object")
     *
     * @param ParamFetcherInterface $paramFetcher
     * @return int
     */
    public function countAction(ParamFetcherInterface $paramFetcher)
    {
        $criteria = $paramFetcher->get('filters', false) ?: array();

        return $this->handler->count($criteria);
    }

    /**
     * @param string $accessToken
     *
     * @return User
     */
    protected function getMe($accessToken)
    {
        /* @var TokenManager $tokenManager */
        $tokenManager = $this->container->get('fos_oauth_server.access_token_manager.default');
        $accessToken  = $tokenManager->findTokenByToken($accessToken);

        if ($accessToken === null) {
            $user = null;
        } else {
            /* @var User $user */
            $user = $accessToken->getUser();
        }

        return $user;
    }

    /**
     * Fetch a Item or throw an 404 Exception.
     *
     * @param mixed $id
     *
     * @return object
     *
     * @throws NotFoundHttpException
     */
    protected function getOr404($id)
    {
        if (!($item = $this->handler->get($id))) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $id));
        }

        return $item;
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

    protected function getBy($criteria, ParamFetcherInterface $paramFetcher, $orderBy = null)
    {
        $offset  = $paramFetcher->get('offset');
        $limit   = $paramFetcher->get('limit');

        if (empty($limit)) {
            $limit = null;
        }

        return $this->handler->getBy($criteria, $orderBy, $limit, $offset);
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
     * Get Pagination of collection
     *
     * @param string $apiName
     * @param int $count
     * @param int $limit
     * @param int $offset
     * @param array $extraParams
     *
     * @return array
     */
    protected function getPagination($apiName, $count, $limit = 5, $offset = 0, $extraParams = [])
    {
        $pages   = ceil($count / $limit);
        $page    = 1 + floor($offset / $limit);
        $nextUrl = null;
        $prevUrl = null;

        if ($limit + $offset < $count) {
            $nextParams = array_merge($extraParams, ['limit' => $limit, 'offset' => $limit + $offset]);
            $nextUrl = $this->generateUrl($apiName, $nextParams);
        }

        if ($offset - $limit > -1) {
            $prevParams = array_merge($extraParams, ['limit' => $limit, 'offset' => $offset - $limit]);
            $prevUrl = $this->generateUrl($apiName, $prevParams);
        }

        return [
            'page'  => $page,
            'pages' => $pages,
            'next'  => $nextUrl,
            'prev'  => $prevUrl,
        ];
    }
}
