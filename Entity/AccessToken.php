<?php

declare(strict_types=1);
/**
 * User: Fabiano Roberto <fabiano.roberto@ped.technology>
 * Date: 08/09/15
 * Time: 12:08
 */

namespace PaneeDesign\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use FOS\OAuthServerBundle\Model\AccessTokenInterface;

/**
 * @ORM\Table("api_access_token")
 * @ORM\Entity
 */
class AccessToken extends BaseAccessToken implements AccessTokenInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="PaneeDesign\ApiBundle\Entity\Client")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="FOS\UserBundle\Model\UserInterface")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     * @ORM\JoinColumn(nullable=true)
     */
    protected $user;
}
