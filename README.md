# openWeathermap.org weather plugin for Craft CMS 3.x

Put weather information from any location in your website.

## Requirements

This plugin requires Craft CMS 3 or later and an active API key from openWeathermap.org

## Overview

Access the weather information via twig functions or ajax call.
You can choose between simplified and raw representation of weather data.
weather data is stored in database and gets updated only when refresh interval has passed in order to save bandwidth.

## Configuration

Fill in the openWeathermap API key to use the plugin.
Setup the refresh interval (minutes) to setup when weather should be updated.
Use a default location (latitude, longitude) to get the weather from.

## Using

There are two ways to use the plugin. 

- via twig functions 
The following function are available:
`craft.openWeather.simplified(lat, lon)` -> Given a latitude and longitude returns the simplified version of weather
`craft.openWeather.raw(lat, lon)` -> Given a latitude and longitude returns the raw version of weather
`craft.openWeather.defaultLocation()` -> Returns the latitude and longitude of the default location setup in the settings plugin

- via http requests
`/api/openWeather/lat-lon/<latitude>,<latitude>,<label>` -> Given a latitude and longitude returns the simplified version of weather data; optionally pass a label parameter to set a custom label for the given location.
`/api/openWeather/default` -> Returns the simplified version of weather data of the default location as setup in the settings

`/api/openWeather/raw/lat-lon/<latitude>,<latitude>,<label>`  -> Given a latitude and longitude returns the raw version of weather data; optionally pass a label parameter to set a custom label for the given location.
`/api/openWeather/raw/default` -> Returns the raw version of weather data of the default location as setup in the settings

Simplified output
```json
{
  "status": "clear",
  "temperature": 22.30,
  "city": "Jordaan",
  "label": "Amsterdam",
  "country_iso": "NL",
  "clear": true,
  "partial-clear": false,
  "clouds": false,
  "hot": false,
  "mist": false,
  "rain": false,
  "thunderstorm": false,
  "snow": false,
  "ice": false,
  "wind": false,
  "wind_direction": "S",
  "text": "Clear",
  "day": true,
  "night": false
}
```

Raw output
```json
{
  "status": "Clear",
  "city": "Jordaan",
  "label": "Amsterdam",
  "country_iso": "NL",
  "temperature": 22,
  "humidity": 56,
  "visibility": 10000,
  "wind_speed": 3,
  "wind_direction": 170,
  "sunrise": "2018-10-16T08:08:24+02:00",
  "sunset": "2018-10-16T18:42:41+02:00"
}
```

## Credits

Made with ❤️ by [Bolden](https://www.bolden.nl) – It's free to use, but if you insist 😄 donate [here](https://www.paypal.me/boldenamsterdam)


