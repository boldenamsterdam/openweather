![Bolden OpenWeather Craft CMS 3 plugin](https://www.bolden.nl/uploads/Bolden-OpenWeather-Craft-CMS-3-plugin.jpg)

# openWeathermap.org weather plugin for Craft CMS 3.x

Integrate weather information from any location right into your Craft CMS 3 website

## Requirements

This plugin requires Craft CMS 3 or later and an active API key from openWeathermap.org

## Overview

Access the weather information via TWIG functions or AJAX calls.
You can choose between simplified and raw representation of weather data.
Weather data is stored in your database and only updates when refresh interval has passed in order to save bandwidth and improve performance.

## Configuration

* Fill in the openWeathermap API key to use the plugin.
* Setup the refresh interval (minutes) to setup when weather should be updated.
* Use a default location (latitude, longitude) to get the weather.

## Using

There are two ways to use the plugin, via __twig__ functions and __HTTP__ requests.  

### Twig functions  
* `craft.openWeather.simplified(lat, lon)`  
Given a latitude and longitude returns the simplified version of weather.
* `craft.openWeather.raw(lat, lon)`  
Given a latitude and longitude returns the raw version of weather.  
* `craft.openWeather.defaultLocation()`  
Returns the latitude and longitude of the default location setup in the settings.

### __HTTP__ requests  
* `/api/openWeather/lat-lon/<latitude>,<latitude>,<label>`  
Given a latitude and longitude returns the simplified version of weather data; optionally pass a label parameter to set a custom label for the given location.
* `/api/openWeather/default`  
Returns the simplified version of weather data of the default location as setup in the settings.  
* `/api/openWeather/raw/lat-lon/<latitude>,<latitude>,<label>`  
Given a latitude and longitude returns the raw version of weather data; optionally pass a label parameter to set a custom label for the given location.  
* `/api/openWeather/raw/default`   
Returns the raw version of weather data of the default location as setup in the settings

### Example

Request  
GET `/api/openWeather/default`  

Response
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

Request  
GET `/api/openWeather/raw/default`  

Response
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

Made with ❤️ by [Bolden](https://www.bolden.nl) and free to use!

