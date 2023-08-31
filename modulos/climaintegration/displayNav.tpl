<!-- displayNav.tpl -->
<style>
.weather-container {
    padding: 5px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    column-gap: 15px;
}

.location {
    font-size: 18px;
    font-weight: bold;
}

.temperature {
    font-size: 18px;
}

.humidity {
    font-size: 14px;
    color: #666;
}
</style>
<div class="weather-container">
    <div class="location">{$weather_data.location}</div>
    <div class="temperature">{$weather_data.temperature}</div>
    <div class="humidity">Humedad: {$weather_data.humidity}</div>
    <img src="https://openweathermap.org/img/wn/{$weather_data.icon}.png" alt="Weather Icon">
</div>
