<?php

/**
 * OpenWeather plugin for Craft CMS 3.x
 *
 * OpenWeather Plugin
 *
 * @link      https://www.bolden.nl
 * @copyright Copyright (c) 2018 Bolden B.V.
 * @author Klearchos Douvantzis
 */

namespace bolden\openweather\controllers;

use Craft;
use craft\web\Controller;
use bolden\openweather\records\OpenWeatherEntry;
use bolden\openweather\OpenWeather;

/**
 * OpenWeather Controller
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var array
     */
    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    /**
     * Returns simplified OpenWeather data from given location
     *
     * @param boolean $simplified
     * @return void
     */
    public function actionLatLonRaw($lat, $lon, $label)
    {
        $this->actionLatLon($lat, $lon, $label, false);
    }

    /**
     * Returns simplified OpenWeather data from default location
     *
     * @param boolean $simplified
     * @return void
     */
    public function actionDefaultRaw()
    {
        $this->actionDefault(false);
    }

    /**
     * Returns OpenWeather data from given location
     *
     * @param boolean $simplified
     * @return void
     */
    public function actionLatLon($lat, $lon, $label, $simplified = true)
    {
        // get default entry from settings
        $openWeatherEntry = OpenWeather::getInstance()->openWeatherService->findWeatherEntry(['lat' => $lat, 'lon' => $lon]);
        
        // update OpenWeather if older that interval
        $openWeatherEntry = OpenWeather::getInstance()->openWeatherService->updateWeatherIfNeeded($openWeatherEntry, $label);
        if ($openWeatherEntry) {
            // define OpenWeather status
            $data = OpenWeather::getInstance()->openWeatherService->defineType($openWeatherEntry, $simplified);
            $this->asJson($data);
        } else {
            $this->asErrorJson('Cannot access OpenWeather entry.');
        }
    }

    /**
     * Returns OpenWeather data from default location
     *
     * @param boolean $simplified
     * @return void
     */
    public function actionDefault($simplified = true)
    {
        // get default entry from settings
        $openWeatherEntry = OpenWeather::getInstance()->openWeatherService->findWeatherEntry();
        
        // update OpenWeather if older that interval
        $openWeatherEntry = OpenWeather::getInstance()->openWeatherService->updateWeatherIfNeeded($openWeatherEntry);
        if ($openWeatherEntry) {
            // define OpenWeather status
            $data = OpenWeather::getInstance()->openWeatherService->defineType($openWeatherEntry, $simplified);
            $this->asJson($data);
        } else {
            $this->asErrorJson('Cannot access default OpenWeather entry.');
        }
    }

}
