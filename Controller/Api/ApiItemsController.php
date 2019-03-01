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

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;

use Swagger\Annotations as SWG;

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
     * List all Items
     *
     * @SWG\Parameter(
     *     in="query",
     *     type="number",
     *     minimum="0",
     *     name="offset",
     *     description="Offset from which to start listing Items.",
     *     required=true,
     *     default="0"
     * )
     * @SWG\Parameter(
     *     in="query",
     *     type="integer",
     *     name="limit",
     *     description="How many Items to return.",
     *     required=true,
     *     default="5"
     * )
     * @SWG\Response(
     *     response="200",
     *     description="Returned when successful",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *             type="string"
     *         )
     *     )
     * )
     *
     * @return array
     */
    public function cgetAction(Request $request)
    {
        $offset = $request->query->get('offset');
        $limit  = $request->query->get('limit');

        return $this->handler->all($limit, $offset);
    }

    /**
     * Get number of Items
     *
     * @SWG\Parameter(
     *     in="query",
     *     type="array",
     *     name="filters[]",
     *     description="Custom filter object",
     *     required=false,
     *     collectionFormat="multi",
     *     @SWG\Items(
     *         type="array",
     *         @SWG\Items(
     *             type="string"
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response="200",
     *     description="Success"
     * )
     *
     * @param Request $request
     * @return int
     */
    public function countAction(Request $request)
    {
        $criteria = $request->query->get('filters', false) ?: array();

        return $this->handler->count($criteria);
    }

    /**
     * Get single Item
     *
     * @SWG\Parameter(
     *     in="path",
     *     type="integer",
     *     name="id",
     *     description="The admin identifier"
     * )
     * @SWG\Response(
     *     response="200",
     *     description="Returned when successful"
     * )
     * @SWG\Response(
     *     response="403",
     *     description="Returned when the admin is not authorized to say hello"
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Returned when the admin is not found"
     * )
     *
     * @param Request $request
     * @param int $id the Item id
     * @return object
     * @throws NotFoundHttpException when Item not exist
     */
    public function getAction(Request $request, $id)
    {
        $item = $this->getOr404($id);

        return $item;
    }

    /**
     * Create a Item
     *
     * @SWG\Parameter(
     *     in="body",
     *     name="form",
     *     description="Item parameters",
     *     @SWG\Items(
     *         type="array",
     *         @SWG\Items(
     *             type="string"
     *         )
     *     ),
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *             type="string"
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success"
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Returned when the form has errors"
     * )
     *
     * @param Request $request the request object
     * @return object
     * @throws \Exception
     */
    public function postAction(Request $request)
    {
        $toPost  = ApiHelper::formatRequestData($request->request->all());
        $newItem = $this->handler->post(
            $toPost
        );

        return $newItem;
    }

    /**
     * Partially update an Item from the submitted data
     *
     * @SWG\Parameter(
     *     in="body",
     *     name="form",
     *     description="Item parameters",
     *     @SWG\Items(
     *         type="array",
     *         @SWG\Items(
     *             type="string"
     *         )
     *     ),
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *             type="string"
     *         )
     *     )
     * ),
     * * @SWG\Parameter(
     *     in="path",
     *     type="integer",
     *     name="id",
     *     description="The item identifier"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success"
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Returned when the form has errors"
     * )
     *
     * @param Request $request the request object
     * @param int $id the Item id
     *
     * @return object
     * @throws NotFoundHttpException when Item not exist
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
     * Delete a Item
     *
     * @SWG\Parameter(
     *     in="path",
     *     type="integer",
     *     name="id",
     *     description="The item identifier"
     * )
     * @SWG\Response(
     *     response="200",
     *     description="Returned when successful"
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Returned when the form has errors"
     * )
     *
     * @param int $id the Item id
     *
     * @return boolean
     */
    public function deleteAction($id)
    {
        return $this->getHandler()->delete($id);
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
     * @param int $id
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

    protected function getBy($criteria, Request $request, $orderBy = null)
    {
        $offset  = $request->query->get('offset');
        $limit   = $request->query->get('limit');

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
        $nextUrl = null;
        $prevUrl = null;

        if ((int) $limit === 0) {
            $pages = 1;
            $page  = 1;
        } else {
            $pages = ceil($count / $limit);
            $page  = 1 + floor($offset / $limit);

            if ($limit + $offset < $count) {
                $nextParams = array_merge($extraParams, ['limit' => $limit, 'offset' => $limit + $offset]);
                $nextUrl = $this->generateUrl($apiName, $nextParams);
            }

            if ($offset - $limit > -1) {
                $prevParams = array_merge($extraParams, ['limit' => $limit, 'offset' => $offset - $limit]);
                $prevUrl = $this->generateUrl($apiName, $prevParams);
            }
        }

        return [
            'page'  => $page,
            'pages' => $pages,
            'next'  => $nextUrl,
            'prev'  => $prevUrl,
        ];
    }
}
