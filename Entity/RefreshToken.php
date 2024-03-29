<?php

declare(strict_types=1);
/**
 * User: Fabiano Roberto <fabiano.roberto@ped.technology>
 * Date: 08/09/15
 * Time: 12:09
 */

namespace PaneeDesign\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\RefreshToken as BaseRefreshToken;
use FOS\OAuthServerBundle\Model\RefreshTokenInterface;

/**
 * @ORM\Table("api_refresh_token")
 * @ORM\Entity
 */
class RefreshToken extends BaseRefreshToken implements RefreshTokenInterface
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
