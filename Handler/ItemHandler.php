<?php

declare(strict_types=1);

/**
 * Fabiano Roberto <fabiano@paneedesign.com>
 * Date: 28/01/19
 * Time: 13:01.
 */

namespace PaneeDesign\ApiBundle\Handler;

use AppBundle\Exception\InvalidFormException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectManagerDecorator;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

final class ItemHandler extends ObjectManagerDecorator implements ItemHandlerInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $formName;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct(ObjectManager $wrapped, FormFactoryInterface $formFactory)
    {
        $this->wrapped = $wrapped;
        $this->formFactory = $formFactory;
    }

    public function setClassName(string $className)
    {
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setFormName(string $formName)
    {
        $this->formName = $formName;
    }

    public function getFormName(): string
    {
        return $this->formName;
    }

    /**
     * Get a Item.
     *
     * @param mixed $id
     *
     * @return object
     */
    public function get($id)
    {
        return $this->find($this->className, $id);
    }

    /**
     * Get list of Item by criteria.
     *
     * @param array $criteria
     * @param array $orderBy
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    public function getBy(array $criteria, array $orderBy = null, $limit = 1, $offset = 0)
    {
        return $this->getRepository($this->className)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Get a Item by criteria.
     *
     * @param array $criteria
     *
     * @return object
     */
    public function getOneBy($criteria)
    {
        return $this->getRepository($this->className)->findOneBy($criteria);
    }

    /**
     * Get a list of Items.
     *
     * @param int $limit  the limit of the result
     * @param int $offset starting from the offset
     *
     * @return array
     */
    public function all($limit = 5, $offset = 0)
    {
        return $this->getRepository($this->className)->findBy([], null, $limit, $offset);
    }

    /**
     * Create a new Item.
     *
     * @param array $parameters
     *
     * @return object
     *
     * @throws \Exception
     */
    public function post(array $parameters)
    {
        $item = $this->createItem();

        return $this->processForm($item, $parameters, 'POST');
    }

    /**
     * Get count of Item by criteria.
     *
     * @param array $criteria
     *
     * @return int
     */
    public function count($criteria = [])
    {
        $elem = $this->getRepository($this->className)->findBy($criteria);

        return count($elem);
    }

    /**
     * Edit a Item.
     *
     * @param int   $id
     * @param array $parameters
     *
     * @return object
     *
     * @throws \Exception
     */
    public function put($id, array $parameters)
    {
        $item = $this->get($id);

        if (null == $item) {
            throw new ResourceNotFoundException();
        }

        return $this->processForm($item, $parameters, 'PUT');
    }

    /**
     * Partially update a Item.
     *
     * @param int   $id
     * @param array $parameters
     *
     * @return object
     *
     * @throws \Exception
     */
    public function patch($id, array $parameters)
    {
        $item = $this->get($id);

        if (null == $item) {
            throw new ResourceNotFoundException();
        }

        return $this->processForm($item, $parameters, 'PATCH');
    }

    /**
     * Delete a Item.
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete($id)
    {
        $item = $this->get($id);

        if (null == $item) {
            throw new ResourceNotFoundException();
        }

        $this->remove($item);
        $this->flush();

        return true;
    }

    /**
     * Processes the form.
     *
     * @param object $item
     * @param array  $parameters
     * @param string $method
     *
     * @return object
     *
     * @throws \Exception
     */
    private function processForm($item, array $parameters, $method = 'PUT')
    {
        if (empty($this->formName)) {
            throw new \Exception('No formName set for ' . get_class());
        }

        $form = $this->formFactory->create($this->formName, $item, ['method' => $method]);
        $form->submit($parameters, 'PATCH' !== $method);

        if ($form->isSubmitted() && $form->isValid()) {
            $item = $form->getData();
            $this->persist($item);
            $this->flush();

            return $item;
        }

        throw new InvalidFormException($form);
    }

    private function createItem()
    {
        return new $this->className();
    }
}
