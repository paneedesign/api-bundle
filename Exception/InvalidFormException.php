<?php

declare(strict_types=1);

/**
 * User: Fabiano Roberto <fabiano@paneedesign.com>
 * Date: 26/02/19
 * Time: 15:00.
 */

namespace PaneeDesign\ApiBundle\Exception;

use Symfony\Component\Form\FormInterface;

class InvalidFormException extends \Exception
{
    /**
     * @var FormInterface
     */
    protected $form;

    public function __construct(FormInterface $form = null)
    {
        parent::__construct('Invalid submitted data');

        $this->form = $form;
    }

    public function getErrors(): array
    {
        return $this->getFormErrors($this->form);
    }

    private function getFormErrors(FormInterface $form)
    {
        $toReturn = [];

        foreach ($form->getErrors() as $error) {
            $detail = ['message' => $error->getMessage()];

            if ($error->getCause() && method_exists($error->getCause(), 'getInvalidValue')) {
                $detail['cause'] = $error->getCause()->getInvalidValue();
            }

            $toReturn[] = $detail;
        }

        /* @var FormInterface $child */
        foreach ($form->all() as $child) {
            if ($child->isSubmitted() && $child->isValid() === false) {
                $childErrorMessages = $this->getFormErrors($child);

                if (!empty($childErrorMessages)) {
                    $toReturn[$child->getName()] = $childErrorMessages;
                }
            }
        }

        return $toReturn;
    }
}
