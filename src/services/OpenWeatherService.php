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

namespace bolden\openweather\services;

use Craft;
use craft\base\Component;
use craft\helpers\FileHelper;
use bolden\openweather\assets\OpenWeatherAssets;
use bolden\openweather\OpenWeather;
use craft\elements\Entry;
use craft\services\Elements;
use yii\base\Event;
use craft\elements\db\ElementQuery;
use bolden\openweather\records\OpenWeatherCache;
use bolden\openweather\records\OpenWeatherElement;
use bolden\openweather\records\OpenWeatherEntry;
use yii\db\IntegrityException;

/**
 * OpenWeather Service
 */
class OpenWeatherService extends Component
{
    private $params = [];
    private $settings = [];

    public function __construct()
    {
        $this->settings = OpenWeather::getInstance()->getSettings();
        $this->params['baseUrl'] = 'http://api.openweathermap.org/data/2.5/weather';
        $this->params['appid'] = $this->settings->openmapsAPIKey;
        $this->params['units'] = 'metric';
        $this->params['lat'] = $this->settings->latitude;
        $this->params['lon'] = $this->settings->longitude;
        parent::__construct();
    }

    /**
     * Searches for the OpenWeather entry in database from the given parameters latitude/longitude or city_name
     * If nothing found fetch default entry from plugin settings
     *
     * @param array $params
     * @return object
     */
    public function findWeatherEntry($params = [])
    {
        // get entry from given lat lon
        if (!empty($params['lat']) && !empty($params['lon'])) {
            $this->params['lat'] = $params['lat'];
            $this->params['lon'] = $params['lon'];
            $openWeatherEntry = OpenWeatherEntry::findOne([
                'lat' => $this->params['lat'],
                'lon' => $this->params['lon']
            ]);
        }
        // get entry from city name
        if (!empty($params['city_name'])) {
            $this->params['city_name'] = $params['city_name'];
            $openWeatherEntry = OpenWeatherEntry::findOne([
                'city_name' => $this->params['city_name']
            ]);
        }
        // get default entry
        if (empty($openWeatherEntry)) {
            $openWeatherEntry = new OpenWeatherEntry();
            $openWeatherEntry->lat = $this->params['lat'];
            $openWeatherEntry->lon = $this->params['lon'];
        }
        return $openWeatherEntry;
    }

    /**
     * Updates the OpenWeather entry in db with new data if old data exist
     *
     * @param object $openWeatherEntry
     * @return object
     */
    public function updateWeatherIfNeeded($openWeatherEntry, $label = '')
    {
        if ($this->needsUpdate($openWeatherEntry)) {
            $openWeather = $this->getWeather();
            if ($openWeather === false) {
                return $openWeatherEntry;
            }
            // check if OpenWeather station already exists to avoid duplicate
            $openWeatherEntryNew = OpenWeatherEntry::findOne([
                'city_id' => $openWeather['id']
            ]);
            if (!empty($openWeatherEntryNew)) {
                $openWeatherEntry = $openWeatherEntryNew;
            }

            // now update entry if OpenWeather info found
            $openWeatherEntry->city_id = $openWeather['id'];
            $openWeatherEntry->city_name = isset($openWeather['name']) ? $openWeather['name'] : '';
            if (empty($openWeatherEntry->label) && empty($label)) {
                $openWeatherEntry->label = $openWeatherEntry->city_name;
            }
            if ($label !== false && !empty($label)) {
                $openWeatherEntry->label = $label;
            }
            $openWeatherEntry->country_iso = isset($openWeather['sys']['country']) ? $openWeather['sys']['country'] : '';
            $openWeatherEntry->weather_status = isset($openWeather['weather'][0]['main']) ? $openWeather['weather'][0]['main'] : '';
            $openWeatherEntry->weather_code = isset($openWeather['weather'][0]['id']) ? $openWeather['weather'][0]['id'] : '';
            $openWeatherEntry->temperature = isset($openWeather['main']['temp']) ? $openWeather['main']['temp'] : '';
            $openWeatherEntry->humidity = isset($openWeather['main']['humidity']) ? $openWeather['main']['humidity'] : '';
            $openWeatherEntry->cloudiness = isset($openWeather['clouds']['all']) ? $openWeather['clouds']['all'] : '';
            $openWeatherEntry->visibility = isset($openWeather['visibility']) ? $openWeather['visibility'] : '';
            $openWeatherEntry->wind_speed = isset($openWeather['wind']['speed']) ? $openWeather['wind']['speed'] : '';
            $openWeatherEntry->wind_direction = isset($openWeather['wind']['deg']) ? $openWeather['wind']['deg'] : '';
            $openWeatherEntry->sunrise = isset($openWeather['sys']['sunrise']) ? date('Y-m-d H:i:s', $openWeather['sys']['sunrise']) : '';
            $openWeatherEntry->sunset = isset($openWeather['sys']['sunset']) ? date('Y-m-d H:i:s', $openWeather['sys']['sunset']) : '';
            if (empty($openWeatherEntry->id)) {
                $openWeatherEntry->save();
            } else {
                $openWeatherEntry->update();
            }
        }
        return $openWeatherEntry;
    }

    /**
     * Formats the url to be requested
     *
     * @return string
     */
    private function formUrl()
    {
        $url = $this->params['baseUrl'];
        $params = [];
        foreach ($this->params as $key => $value) {
            $params[] = $key . '=' . $value;
        }
        return $url . '?' . implode('&', $params);
    }

    /**
     * Performs the request to the API
     *
     * @param array $params
     * @return array
     */
    private function getWeather($params = [])
    {
        $url = $this->formUrl();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // check response http code
        if ($http_code === 200) {
            $openWeather = json_decode($output, true);
            // check if OpenWeather station is found
            if ($openWeather['cod'] === 200) {
                return $openWeather;
            }
        }
        return false;
    }

    /**
     * Check whether db OpenWeather entry needs to be updated
     *
     * @param object $openWeatherEntry
     * @return boolean
     */
    private function needsUpdate($openWeatherEntry)
    {
        $datetime = new \DateTime("+ {$this->settings->updateInterval} minutes");
        if ($openWeatherEntry->dateUpdated <= $datetime) {
            return true;
        }
        return false;
    }

    /**
     * Formats raw data from db entry to be returned
     *
     * @param [type] $entry
     * @param boolean $simplified
     * @return void
     */
    public function defineType($entry, $simplified = true)
    {
        $sunrise = new \DateTime($entry->sunrise);
        $sunriseTime = $sunrise->format(\DateTime::W3C);

        $sunset = new \DateTime($entry->sunset);
        $sunsetTime = $sunset->format(\DateTime::W3C);

        $datetime = new \DateTime();
        if ($simplified) {
            $data = $this->simplifiedWeather($entry);
            $data['day'] = $datetime->format("Hi") < $sunset->format("Hi") && $datetime->format("Hi") > $sunrise->format("Hi");
            $data['night'] = !$data['day'];
        } else {
            $data = [
                'status' => $entry->weather_status,
                'code' => $entry->weather_code,
                'city' => $entry->city_name,
                'label' => $entry->label,
                'country_iso' => $entry->country_iso,
                'temperature' => $entry->temperature,
                'humidity' => $entry->humidity,
                'visibility' => $entry->visibility,
                'wind_speed' => $entry->wind_speed,
                'wind_direction' => $entry->wind_direction,
                'sunrise' => $sunriseTime,
                'sunset' => $sunsetTime
            ];
        }
        return $data;
    }

    /**
     * Format db entry to a simplified version
     *
     * @param object $entry
     * @return array
     */
    private function simplifiedWeather($entry)
    {
        $data = [
            'status' => '',
            'temperature' => $entry->temperature,
            'city' => $entry->city_name,
            'label' => $entry->label,
            'country_iso' => $entry->country_iso,
            'clear' => $entry->weather_code === 800,
            'partial-clear' => $entry->weather_code === 801, // clear sky or few clouds with low cloudiness
            'clouds' => $entry->weather_code > 801, // clouds or few clouds with high cloudiness
            'hot' => $entry->temperature >= 25, // more than 25 C degrees
            'mist' => $entry->visibility <= 1000 || ($entry->weather_code >= 700 && $entry->weather_code < 800), // less than 1000m visibility or atmosphere incident
            'rain' => $entry->weather_code >= 300 && $entry->weather_code < 600, // rain or drizzle
            'thunderstorm' => $entry->weather_code >= 200 && $entry->weather_code < 300, // thunderstorm
            'snow' => $entry->weather_code >= 600 && $entry->weather_code < 700, // snow or snow & rain
            'ice' => $entry->temperature < 5, // temperature lower than 5 C degrees
            'wind' => $entry->wind_speed > 8 // 5 bft or greater
        ];

        // convert meteorogical degrees to compas North/East/South/West directions
        if ($entry->wind_direction > 337.5) {
            $data['wind_direction'] = 'N';
        } else if ($entry->wind_direction > 292.5) {
            $data['wind_direction'] = 'NW';
        } else if ($entry->wind_direction > 247.5) {
            $data['wind_direction'] = 'W';
        } else if ($entry->wind_direction > 202.5) {
            $data['wind_direction'] = 'SW';
        } else if ($entry->wind_direction > 157.5) {
            $data['wind_direction'] = 'S';
        } else if ($entry->wind_direction > 122.5) {
            $data['wind_direction'] = 'SE';
        } else if ($entry->wind_direction > 67.5) {
            $data['wind_direction'] = 'E';
        } else if ($entry->wind_direction > 22.5) {
            $data['wind_direction'] = 'NE';
        } else {
            $data['wind_direction'] = 'N';
        }

        // set a status based on logic before
        if ($data['clear']) {
            $data['status'] = 'clear';
        }
        if ($data['partial-clear']) {
            $data['status'] = 'partial-clear';
        }
        if ($data['clouds']) {
            $data['status'] = 'clouds';
        }
        if ($data['rain']) {
            $data['status'] = 'rain';
        }
        if ($data['thunderstorm']) {
            $data['status'] = 'thunderstorm';
        }
        if ($data['snow']) {
            $data['status'] = 'snow';
        }
        if ($data['ice']) {
            $data['status'] = 'ice';
        }
        if ($data['mist']) {
            $data['status'] = 'mist';
        }
        // translation status
        $data['text'] = \Craft::t('open-weather', $data['status']);
        return $data;
    }
}
