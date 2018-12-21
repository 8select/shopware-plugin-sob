<?php

use CseEightselectBasic\Services\Config\Validator;

class Shopware_Controllers_Backend_CseEightselectBasicConfigValidation extends \Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var Validator
     */
    private $configValidator;

    public function __construct(\Enlight_Controller_Request_Request $request, \Enlight_Controller_Response_Response $response)
    {
        $this->configValidator = Shopware()->Container()->get('cse_eightselect_basic.config.validator');

        parent::__construct($request, $response);
    }

    public function validateAction()
    {
        $validationResult = $this->configValidator->validateConfig();

        $this->View()->assign(['validationResult' => $validationResult]);
    }
}
