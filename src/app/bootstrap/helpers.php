<?php

require_once (__DIR__ . '/../../Domains/Common/helpers.php');

if(!function_exists('swagger_lume_asset_fixed')) {
    /**
     * Returns asset from swagger-ui composer package.
     *
     * @param $asset string
     * @return string
     *
     * @throws \SwaggerLume\Exceptions\SwaggerLumeException
     */
    function swagger_lume_asset_fixed($asset, bool $forceHttps = true)
    {
        $file = swagger_ui_dist_path($asset);
        $secure = app('request')->secure();

        return route('swagger-lume.asset', ['asset' => $asset, 'v' => md5($file)], $secure);
    }
}
