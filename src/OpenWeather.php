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

namespace bolden\openweather;


use Craft;
use craft\base\Plugin;
use craft\web\Response;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\services\Elements;
use craft\web\UrlManager;
use craft\helpers\FileHelper;
use craft\helpers\UrlHelper;
use craft\events\RegisterUrlRulesEvent;
use bolden\openweather\services\OpenWeatherService;
use bolden\openweather\models\OpenWeatherSettings;

use yii\base\Event;
use craft\elements\db\ElementQuery;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\Asset;
use bolden\openweather\records\OpenWeatherEntry;
use craft\elements\User;
use craft\elements\GlobalSet;
use craft\web\twig\variables\CraftVariable;
use bolden\openweather\variables\OpenWeatherVariable;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Bolden B.V.
 * @package   OpenWeather
 * @since     0.0.1
 *
 */
class OpenWeather extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * OpenWeather::$plugin
     *
     * @var OpenWeather
     */
    public static $plugin;
    public $schemaVersion = '1.0.0';
    public $allowAnonymous = true;
    public $hasCpSettings = true;

    // Public Methods
    // =========================================================================

    /**
     * Returns whether the plugin should get its own tab in the CP header.
     *
     * @return bool
     */
    public function hasCpSection()
    {
        return false;
    }

    public function hasSettings()
    {
        return true;
    }

    /**
     * @return OpenWeatherSettings
     */
    protected function createSettingsModel()
    {
        return new OpenWeatherSettings();
    }

    /**
     * @return string
     * @throws \yii\base\Exception
     * @throws \Twig_Error_Loader
     * @throws \RuntimeException
     */
    protected function settingsHtml() : string
    {
        return \Craft::$app->getView()->renderTemplate(
            'open-weather/_settings',
            [
                'settings' => $this->getSettings(),
            ]
        );
    }

    /**
     * Init plugin and initiate events
     */
    public function init()
    {
        parent::init();
        $this->setComponents(
            [
                'openWeatherService' => OpenWeatherService::class,
            ]
        );
        self::$plugin = $this;

        if ($this->isInstalled && $this->getSettings()->enableGeneral == 1) {
            // setup url endpoints
            Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function (RegisterUrlRulesEvent $event) {
                $event->rules['api/openWeather/lat-lon/<lat:[+-]?([0-9]*[.])?[0-9]+>,<lon:[+-]?([0-9]*[.])?[0-9]+>,?<label:.*?>'] = 'open-weather/default/lat-lon';
                $event->rules['api/openWeather/default'] = 'open-weather/default/default';
                $event->rules['api/openWeather/raw/lat-lon/<lat:[+-]?([0-9]*[.])?[0-9]+>,<lon:[+-]?([0-9]*[.])?[0-9]+>,?<label:.*?>'] = 'open-weather/default/lat-lon-raw';
                $event->rules['api/openWeather/raw/default'] = 'open-weather/default/default-raw';
            });

            // setup twig variable
            Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('openWeather', OpenWeatherVariable::class);
            });
        }
    }
    
    // Protected Methods
    // =========================================================================

}
            
            