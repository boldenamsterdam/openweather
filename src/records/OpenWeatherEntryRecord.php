<?php
/**
 * OpenWeather plugin for Craft CMS 3.x
 *
 * OpenWeather Caches Record
 *
 * @link      https://www.bolden.nl
 * @copyright Copyright (c) 2018 Bolden B.V.
 * @author Klearchos Douvantzis
 */

namespace bolden\openweather\records;

use craft\db\ActiveRecord;

/**
 * Element record class.
 *
 * @property int $id ID
 * @property float $lat The longitude of the location
 * @property float $lon The latitude of the location
 * @property string $city_name The city name
 * @property string $city_id The city id
 * @property string $country_iso The ISO 3166 country code
 * @property string $openWeather_status The current OpenWeather status
 * @property integer $openWeather_code The OpenWeather code as described in https://openOpenWeathermap.org/OpenWeather-conditions
 * @property float $temperature The temperature in metric system
 * @property integer $humidity The percentage of humidity
 * @property integer $cloudiness The percentage of clouds
 * @property integer $visibility The distance of visibility in metric system
 * @property float $wind_speed The speed of wind in metric system
 * @property integer $wind_direction The direction of the wind in degrees
 * @property timestamp $sunrise The sunrise in timestamp
 * @property timestamp $sunset The sunset in timestamp
 */
class OpenWeatherEntry extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%open_weather_entries}}';
    }
}
