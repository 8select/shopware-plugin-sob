<?php

namespace CseEightselectBasic\Services\Request;

use CseEightselectBasic\Services\PluginConfig\PluginConfig;
use CseEightselectBasic\Services\Request\AuthException;
use CseEightselectBasic\Services\Request\NotAuthorizedException;

class Auth
{
    /**
     * @var PluginConfig
     */
    private $pluginConfig;

    /**
     * @param PluginConfig $pluginConfig
     */
    public function __construct(PluginConfig $pluginConfig)
    {
        $this->pluginConfig = $pluginConfig;
    }

    /**
     * @var \Enlight_Controller_Request_Request $request
     *
     * @throws AuthException
     * @throws NotAuthorizedException
     */
    public function auth($request)
    {
        $tenantId = $request->getHeader('8select-com-tid');
        $feedId = $request->getHeader('8select-com-fid');

        if (!$tenantId || !$feedId) {
            throw new NotAuthorizedException('hide me', 404);
        }

        $pluginApiId = $this->pluginConfig->get('CseEightselectBasicApiId');
        $pluginFeedId = $this->pluginConfig->get('CseEightselectBasicFeedId');

        if (!$pluginApiId || !$pluginFeedId) {
            throw new AuthException('credentials not configured in plugin', 500);
        }

        if ($tenantId !== $pluginApiId || $feedId !== $pluginFeedId) {
            throw new AuthException('credential mismatch', 403);
        }
    }
}
