<?php
/**
 * Created by PhpStorm.
 * User: fabianoroberto
 * Date: 08/09/15
 * Time: 12:09
 */

namespace PaneeDesign\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use FOS\OAuthServerBundle\Entity\AuthCode as BaseAuthCode;
use FOS\OAuthServerBundle\Model\AuthCodeInterface;

/**
 * @author Fabiano Roberto <fabiano@paneedesign.com>
 *
 * @ORM\Table("api_auth_code")
 * @ORM\Entity
 */
class AuthCode extends BaseAuthCode implements AuthCodeInterface
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
     * @ORM\ManyToOne(targetEntity="PaneeDesign\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     * @ORM\JoinColumn(nullable=true)
     */
    protected $user;
}
