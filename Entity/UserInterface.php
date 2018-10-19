<?php

/**
 * Created by PhpStorm.
 * User: Fabiano Roberto <fabiano@paneedesign.com>
 * Date: 19/10/2018
 * Time: 08:46
 */

namespace PaneeDesign\ApiBundle\Entity;

interface UserInterface
{
    /**
     * Returns the user unique id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Gets the canonical email in search and sort queries.
     *
     * @return string
     */
    public function getEmailCanonical();

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt();
}