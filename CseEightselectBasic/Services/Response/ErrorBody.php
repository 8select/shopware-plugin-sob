<?php

namespace CseEightselectBasic\Services\Response;

class ErrorBody
{
    public function getInternalServerErrorBody($message)
    {
        return json_encode($this->getError('Internal Server Error', $message));
    }

    public function getBadRequestBody($message)
    {
        return json_encode($this->getError('Bad Request', $message));
    }

    public function getError($error, $message)
    {
        return ['error' => $error, 'message' => $message];
    }
}
