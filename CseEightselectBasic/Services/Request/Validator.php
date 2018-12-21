<?php

namespace CseEightselectBasic\Services\Request;

class Validator
{
    /**
     * @var PlugincConfig
     */
    private $pluginConfig;

    /**
     * @var int
     */
    private $MAX_LIMIT = 100;

    /**
     * @param PlugincConfig $pluginConfig
     */
    public function __construct($pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
    }

    public function checkAuthorizationForExport($request)
    {
        $violations = [];

        array_push($violations, $this->validateTenantId($request));
        array_push($violations, $this->validateFeedId($request));

        $violationsWithoutNull = array_filter($violations);

        return [
      'isAuthorized' => empty($violationsWithoutNull),
      'violations' => $violationsWithoutNull,
    ];
    }

    public function checkQueryStringParamsForExport($request)
    {
        $violations = [];

        array_push($violations, $this->validateOffset($request));
        array_push($violations, $this->validateLimit($request));
        array_push($violations, $this->validateFormat($request));

        $violationsWithoutNull = array_filter($violations);

        return [
      'isValid' => empty($violationsWithoutNull),
      'violations' => $violationsWithoutNull,
    ];
    }

    private function validateTenantId($request)
    {
        $tenantId = $request->getHeader('8select-com-tid');

        if (!$tenantId) {
            return '8select-com-tid fehlt im Request Header.';
        }

        $pluginApiId = $this->pluginConfig->get('CseEightselectBasicApiId');

        if ($tenantId !== $pluginApiId) {
            return '8select-com-tid stimmt nicht mit CseEightselectBasicApiId überein.';
        }
    }

    private function validateFeedId($request)
    {
        $feedId = $request->getHeader('8select-com-fid');

        if (!$feedId) {
            return '8select-com-fid fehlt im Request Header.';
        }

        $pluginFeedId = $this->pluginConfig->get('CseEightselectBasicFeedId');

        if ($feedId !== $pluginFeedId) {
            return '8select-com-fid stimmt nicht mit CseEightselectBasicFeedId überein.';
        }
    }

    private function validateOffset($request)
    {
        if ($this->getIntFromGetParam('offset', $request, 0) === null) {
            return 'offset ist kein Integer.';
        }
    }

    private function validateLimit($request)
    {
        $limit = $this->getIntFromGetParam('limit', $request, 50);

        if ($limit === null) {
            return 'limit ist kein Integer.';
        }

        if ($limit > $this->MAX_LIMIT) {
            return "limit darf nicht $this->MAX_LIMIT übersteigen.";
        }
    }

    private function validateFormat($request)
    {
        $format = $request->getParam('format');
        if (!$format) {
            return 'format fehlt im Request Query String Parameter.';
        }

        $validFormats = ['product_feed', 'property_feed', 'status_feed'];

        if (!in_array($format, $validFormats, true)) {
            return "$format is kein validates format.";
        }
    }

    private function getIntFromGetParam($parameterName, $request, $default)
    {
        $value = $request->getParam($parameterName, $default);

        if (!is_numeric($value)) {
            return null;
        }

        return $value;
    }
}
