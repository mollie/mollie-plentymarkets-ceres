<?php

namespace Mollie\Validators;

use Plenty\Validation\Validator;

/**
 * Class SaveMethodValidator
 * @package Mollie\Validators
 */
class SaveMethodSettingValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function buildCustomMessages()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    protected function defineAttributes()
    {
        $this->addString('id', true);
        $this->addBool('isActive', true);
        $this->add('names')->isArray();
        $this->addInt('position');
        $this->addString('description', true);
    }
}