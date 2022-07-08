<?php

namespace LaravelAds;

use LaravelAds\Services\BingAds\Service as BingAdsService;
use LaravelAds\Services\FacebookAds\Service as FacebookAdsService;

class LaravelAds
{
    /**
     * service()
     *
     * @return static method
     */
    public static function service($service)
    {
        if ($service == 'BingAds') 
            return static::bingAds();

        if ($service == 'FacebookAds') 
            return static::facebookAds();
    }

    /**
     * bingAds()
     * Bind Ads
     *
     */
    public static function bingAds() {
        return (new BingAdsService());
    }

    /**
     * facebookAds()
     * Facebook Ads
     *
     */
    public static function facebookAds() {
        return (new FacebookAdsService());
    }
}
