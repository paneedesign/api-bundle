<?php
/**
 * Created by PhpStorm.
 * User: Fabiano Roberto <fabiano@paneedesign.com>
 * Date: 08/09/15
 * Time: 12:07
 */

namespace PaneeDesign\ApiBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use FOS\OAuthServerBundle\Model\ClientInterface;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Fabiano Roberto <fabiano@paneedesign.com>
 *
 * @ORM\Table("api_client")
 * @ORM\Entity
 */
class Client extends BaseClient implements ClientInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    public function getPublicId()
    {
        return sprintf('%s_%s', $this->getId(), $this->getRandomId());
    }
}
