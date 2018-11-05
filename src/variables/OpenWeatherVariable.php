<?php

/**
 * OpenWeather plugin for Craft CMS 3.x
 *
 * OpenWeather Service
 *
 * @link      https://www.bolden.nl
 * @copyright Copyright (c) 2018 Bolden B.V.
 * @author Klearchos Douvantzis
 */

namespace bolden\openweather\variables;

use Craft;
use bolden\openweather\OpenWeather;

/**
 * OpenWeather Variable
 */
class OpenWeatherVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Gets the simplified form of OpenWeather data
     * {{ craft.openWeather.simplified(lat, lon) }}
     *
     * @param float $lat latitude
     * @param float $lon longitude
     * @return array
     */
    public function simplified($lat, $lon)
    {
        $data = $this->getData($lat, $lon, true);
        return $data;
    }

    /**
     * Gets the default lat/lon
     * {{ craft.openWeather.defaultLocation() }}
     *
     * @return array
     */
    public function defaultLocation()
    {
        $settings = OpenWeather::getInstance()->getSettings();
        return ['latitude' => $settings->latitude, 'longitude' => $settings->longitude];
    }

    /**
     * Gets the raw form of OpenWeather data
     * {{ craft.openWeather.raw(lat, lon) }}
     *
     * @param float $lat latitude
     * @param float $lon longitude
     * @return array
     */
    public function raw($lat, $lon)
    {
        $data = $this->getData($lat, $lon, false);
        return $data;
    }

    /**
     * Gets the OpenWeather data and updated if needed given the latitude/longitude parameters
     *
     * @param float $lat    latitude
     * @param float $lon    longitude
     * @param boolean $simplified   OpenWeather simplified version or not
     * @return array
     */
    private function getData($lat, $lon, $simplified)
    {
        $data = [];
        // get default entry from settings
        $weatherEntry = OpenWeather::getInstance()->openWeatherService->findWeatherEntry(['lat' => $lat, 'lon' => $lon]);
        // update OpenWeather if older that interval
        $weatherEntry = OpenWeather::getInstance()->openWeatherService->updateWeatherIfNeeded($weatherEntry);
        if ($weatherEntry) {
            // define OpenWeather status
            $data = OpenWeather::getInstance()->openWeatherService->defineType($weatherEntry, $simplified);
        }
        return $data;
    }
}
