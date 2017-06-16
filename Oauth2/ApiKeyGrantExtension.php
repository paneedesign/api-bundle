<?php
/**
 * Created by PhpStorm.
 * User: fabianoroberto
 * Date: 11/09/15
 * Time: 14:19
 */

namespace PaneeDesign\ApiBundle\OAuth2;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use OAuth2\Model\IOAuth2Client;

class ApiKeyGrantExtension implements GrantExtensionInterface
{
    /**
     * @var EntityRepository
     */
    private $userRepository;

    /**
     * ApiKeyGrantExtension constructor.
     *
     * @param EntityManager $em
     * @param string $entityClass
     */
    public function __construct(EntityManager $em, $entityClass)
    {
        $this->userRepository = $em->getRepository($entityClass);
    }

    /*
     * {@inheritdoc}
     */
    public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders)
    {
        $user = $this->userRepository->findOneBy(['salt' => $inputData['api_key']]);

        if ($user) {
            //if you need to return access token with associated user
            return ['data' => $user];
        }

        return false;
    }
}