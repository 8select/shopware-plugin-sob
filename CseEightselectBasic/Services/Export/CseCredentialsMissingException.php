<?php

namespace CseEightselectBasic\Services\Export;

class CseCredentialsMissingException extends \Exception
{
    public function __construct()
    {
        parent::__construct('can not connect/disconnect because CSE credentials are not configured');
    }
}
