<?php
/**
 * Created by PhpStorm.
 * User: luigi
 * Date: 31/08/15
 * Time: 09:13
 */

namespace PaneeDesign\ApiBundle\Handler;

interface ItemHandlerInterface
{
    public function setClassName(string $className);

    public function getClassName(): string;

    public function setFormName(string $formName);

    public function getFormName(): string;

    /**
     * Get a Item given the identifier
     *
     * @api
     *
     * @param mixed $id
     *
     * @return object
     */
    public function get($id);

    /**
     * Get a list of Items.
     *
     * @param int $limit  the limit of the result
     * @param int $offset starting from the offset
     *
     * @return array
     */
    public function all($limit = 5, $offset = 0);

    /**
     * Post Item, creates a new Item.
     *
     * @api
     *
     * @param array $parameters
     *
     * @return object
     */
    public function post(array $parameters);

    /**
     * Edit a Item.
     *
     * @api
     *
     * @param int   $id
     * @param array $parameters
     *
     * @return object
     */
    public function put($id, array $parameters);

    /**
     * Partially update a Item.
     *
     * @api
     *
     * @param int   $id
     * @param array $parameters
     *
     * @return object
     */
    public function patch($id, array $parameters);

    /**
     * Delete a Item.
     *
     * @api
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete($id);
}
