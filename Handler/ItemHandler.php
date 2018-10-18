<?php
/**
 * Created by PhpStorm.
 * Item: luigi
 * Date: 31/08/15
 * Time: 09:09
 */

namespace PaneeDesign\ApiBundle\Handler;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

use PaneeDesign\ApiBundle\Exception\InvalidFormException;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class ItemHandler implements ItemHandlerInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;
    protected $entityClass;
    protected $entityForm;

    /**
     * @var ObjectRepository
     */
    protected $repository;
    protected $formFactory;
    protected $container;

    protected $accessToken;
    protected $locale;

    public function __construct(ObjectManager $om, ContainerInterface $container, FormFactoryInterface $formFactory)
    {
        $this->om          = $om;
        $this->container   = $container;
        $this->formFactory = $formFactory;
    }

    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
        $this->repository  = $this->om->getRepository($this->entityClass);
    }

    /**
     * @return ClassMetadata
     */
    public function getClassMetadata()
    {
        return $this->om->getClassMetadata($this->entityClass);
    }

    public function setEntityForm($entityForm)
    {
        $this->entityForm = $entityForm;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return ObjectManager
     */
    public function getEntityManager()
    {
        return $this->om;
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
        return $this->repository->find($id);
    }

    /**
     * Get list of Item by criteria.
     *
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getBy(array $criteria, array $orderBy = null, $limit = 1, $offset = 0)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
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
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Get a list of Items.
     *
     * @param int $limit the limit of the result
     * @param int $offset starting from the offset
     *
     * @return array
     */
    public function all($limit = 5, $offset = 0)
    {
        return $this->repository->findBy([], null, $limit, $offset);
    }

    /**
     * Create a new Item.
     *
     * @param array $parameters
     *
     * @return object
     * @throws \Exception
     */
    public function post(array $parameters)
    {
        $Item = $this->createItem();

        return $this->processForm($Item, $parameters, 'POST');
    }

    /**
     * Get count of Item by criteria.
     *
     * @param array $criteria
     * @return int
     */
    public function count($criteria = [])
    {
        $elem = $this->repository->findBy($criteria);

        return count($elem);
    }

    /**
     * Edit a Item.
     *
     * @param int $id
     * @param array $parameters
     *
     * @return object
     * @throws \Exception
     */
    public function put($id, array $parameters)
    {
        $Item = $this->get($id);

        if ($Item == null) {
            throw new ResourceNotFoundException();
        }

        return $this->processForm($Item, $parameters, 'PUT');
    }

    /**
     * Partially update a Item.
     *
     * @param int $id
     * @param array $parameters
     *
     * @return object
     * @throws \Exception
     */
    public function patch($id, array $parameters)
    {
        $Item = $this->get($id);

        if ($Item == null) {
            throw new ResourceNotFoundException();
        }

        return $this->processForm($Item, $parameters, 'PATCH');
    }

    /**
     * Delete a Item.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $Item = $this->get($id);

        if ($Item == null) {
            throw new ResourceNotFoundException();
        }

        $this->getEntityManager()->remove($Item);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * Processes the form.
     *
     * @param object $item
     * @param array $parameters
     * @param String $method
     * @return object
     * @throws \Exception
     */
    protected function processForm($item, array $parameters, $method = "PUT")
    {
        if (empty($this->entityForm)) {
            throw new \Exception("No entityForm set for ".get_class());
        }

        $form = $this->formFactory->create($this->entityForm, $item, ['method' => $method]);
        $form->submit($parameters, 'PATCH' !== $method);

        if ($form->isValid()) {
            $item = $form->getData();
            $this->om->persist($item);
            $this->om->flush();

            return $item;
        }

        throw new InvalidFormException('Invalid submitted data', $form);
    }

    private function createItem()
    {
        return new $this->entityClass();
    }
}
