<?php

/**
 * OpenWeather plugin for Craft CMS 3.x
 *
 * OpenWeather install migration
 *
 * @link      https://www.bolden.nl
 * @copyright Copyright (c) 2018 Bolden B.V.
 * @author Klearchos Douvantzis
 */

namespace bolden\openweather\migrations;

use Craft;
use craft\db\Migration;

/**
 * Installation Migration
 *
 * @author bolden
 */
class Install extends Migration
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropTableIfExists('{{%open_weather_entries}}');
        
        // create table caches
        $columns = [
            'id' => $this->primaryKey(),
            'lat' => $this->decimal(10, 8),
            'lon' => $this->decimal(11, 8),
            'city_id' => $this->integer(),
            'city_name' => $this->string(55),
            'label' => $this->string(55),
            'country_iso' => $this->string(3),
            'weather_status' => $this->string(55),
            'weather_code' => $this->integer(4),
            'temperature' => $this->decimal(4, 1),
            'cloudiness' => $this->integer(3),
            'humidity' => $this->integer(3),
            'visibility' => $this->integer(5),
            'wind_speed' => $this->decimal(4, 1),
            'wind_direction' => $this->integer(3),
            'sunrise' => $this->timestamp(),
            'sunset' => $this->timestamp(),
            'dateCreated' => $this->dateTime(),
            'dateUpdated' => $this->dateTime(),
            'uid' => $this->uid()
        ];
        $this->createTable('{{%open_weather_entries}}', $columns);

        // create indexes
        $this->createIndex('open_weather_entries_lat_lon_idx', '{{%open_weather_entries}}', ['lat', 'lon'], true);
        $this->createIndex('open_weather_entries_city_id_idx', '{{%open_weather_entries}}', ['city_id'], true);
        $this->createIndex('open_weather_entries_city_name_country_idx', '{{%open_weather_entries}}', ['city_name', 'country_iso']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%open_weather_entries}}');
    }
}
