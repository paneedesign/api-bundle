<?php
/**
 * Fabiano Roberto <fabiano@paneedesign.com>
 * Date: 28/01/19
 * Time: 13:01.
 */

namespace PaneeDesign\ApiBundle\Handler;

use Symfony\Component\HttpFoundation\Request;

class RestApiHandler
{
    /**
     * @var ItemHandlerInterface
     */
    private $handler;

    /**
     * RestApiHandler constructor.
     *
     * @param ItemHandlerInterface $handler
     */
    public function __construct(ItemHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param $className
     *
     * @return $this
     */
    public function setClassName($className)
    {
        $this->handler->setClassName($className);

        return $this;
    }

    public function getClassName()
    {
        return $this->handler->getClassName();
    }

    /**
     * @param $formName
     *
     * @return $this
     */
    public function setFormName($formName)
    {
        $this->handler->setFormName($formName);

        return $this;
    }

    /**
     * @param $id
     *
     * @return object
     */
    public function get($id)
    {
        return $this->handler->get($id);
    }

    /**
     * @param Request $request
     *
     * @return array|object|null
     */
    public function post(Request $request)
    {
        $toPost = $this->formatRequestData($request->request->all());
        $newItem = $this->handler->post(
            $toPost
        );

        return $newItem;
    }

    /**
     * @param Request $request
     * @param $entityId
     *
     * @return object
     */
    public function put(Request $request, $entityId)
    {
        $toPut = $this->formatRequestData($request->request->all());
        $item = $this->handler->put(
            $entityId,
            $toPut
        );

        return $item;
    }

    /**
     * @param Request $request
     * @param $entityId
     *
     * @return object
     */
    public function patch(Request $request, $entityId)
    {
        $toPatch = $this->formatRequestData($request->request->all());
        $item = $this->handler->patch(
            $entityId,
            $toPatch
        );

        return $item;
    }

    /**
     * Delete an Item.
     *
     * @param int $id the Item id
     *
     * @return bool
     */
    public function delete($id)
    {
        return $this->handler->delete($id);
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function formatRequestData($data)
    {
        $formattedData = [];

        foreach ($data as $key => $value) {
            if ('_format' === $key) {
                continue;
            }

            $formattedData[$key] = $value;

            if ('true' === $value) {
                $formattedData[$key] = true;
            } elseif ('false' === $value) {
                $formattedData[$key] = false;
            } elseif ('null' === $value) {
                $formattedData[$key] = null;
            } elseif ('[]' === $value) {
                $formattedData[$key] = [];
            } elseif (true === is_array($value)) {
                $formattedData[$key] = $this->formatRequestData($value);
            }
        }

        return $formattedData;
    }
}
