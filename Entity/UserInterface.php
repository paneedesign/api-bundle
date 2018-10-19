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
}