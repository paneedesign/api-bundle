<?php

declare(strict_types=1);

/**
 * User: Fabiano Roberto <fabiano@paneedesign.com>
 * Date: 26/02/19
 * Time: 15:00.
 */

namespace PaneeDesign\ApiBundle\Exception;

class EntityNotFoundException extends \Exception
{
    /**
     * @var int|string
     */
    private $entityId;

    /**
     * @var string
     */
    private $entityType;

    /**
     * EntityNotFoundException constructor.
     *
     * @param string     $entity
     * @param string|int $identifier
     */
    public function __construct(string $entity, $identifier)
    {
        parent::__construct('Entity not found');

        $this->entityId = $identifier;
        $this->entityType = $this->stripNamespaceFromClassName($entity);
    }

    public function getType(): string
    {
        return 'ENTITY_NOT_FOUND';
    }

    public function getParameters(): array
    {
        return [
            'ENTITY_ID' => $this->entityId,
            'ENTITY_TYPE' => $this->entityType,
        ];
    }

    private function stripNamespaceFromClassName(string $className): string
    {
        return substr($className, strrpos($className, '\\') - strlen($className) + 1);
    }
}
