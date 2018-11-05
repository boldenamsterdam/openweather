<?php
/**
 * OpenWeather plugin for Craft CMS 3.x
 *
 * OpenWeather model settings
 *
 * @link      https://www.bolden.nl
 * @copyright Copyright (c) 2018 Bolden B.V.
 * @author Klearchos Douvantzis
 */

namespace bolden\openweather\models;

class OpenWeatherSettings extends \craft\base\Model
{
    public $enableGeneral = 1;
    public $openmapsAPIKey = '';
    public $longitude = '';
    public $latitude = '';
    public $updateInterval = '30';

    public function rules() {
        return [
            [ ['openmapsAPIKey'],  'required' ],
            [ ['enableGeneral'], 'boolean' ],
            [ ['openmapsAPIKey'], 'string' ],
            [ ['longitude', 'latitude'], 'number' ],
            [ ['updateInterval'], 'integer']
        ];
    }
}
