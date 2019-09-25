<?php

declare(strict_types=1);

/**
 * User: Fabiano Roberto <fabiano.roberto@ped.technology>
 * Date: 31/08/15
 * Time: 09:27
 */

namespace PaneeDesign\ApiBundle\Exception;

class InvalidFormException extends \RuntimeException
{
    protected $form;

    public function __construct($message, $form = null)
    {
        parent::__construct($message);

        $this->form = $form;
    }

    /**
     * @return array|null
     */
    public function getForm()
    {
        return $this->form;
    }
}
