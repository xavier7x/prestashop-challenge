<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class climaintegration extends Module
{
    public function __construct()
    {
        $this->name = 'climaintegration';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Xavier Moreno';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Clima Integration');
        $this->description = $this->l('Mostrar el clima');
    }

    // Hook para mostrar datos climatológicos en el header
    public function hookDisplayNav($params)
    {
        $ip = $this->getIP();
        $location = $this->getUserLocation($ip);

        // Realiza la llamada a la API del tiempo y obtén los datos climatológicos
        $weather_data = $this->getWeatherData($location);
        
        $this->context->controller->registerStylesheet(
            'module-climaintegration', // Identificador único para el estilo
            'modules/' . $this->name . '/css/climaintegration.css', // Ruta al archivo CSS
            ['media' => 'all', 'priority' => 150] // Opciones del estilo
        );
        
        $this->context->smarty->assign('weather_data', $weather_data);

        return $this->display(__FILE__, 'displayNav.tpl');
    }

    private function getIP()
    {
        $ipaddress = '';

        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        elseif (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        elseif (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        elseif (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        elseif (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        elseif (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    private function getUserLocation($ip)
{
    $location = array(
        'city' => 'Guayaquil',
        'country' => 'Ecuador'
    );
    $token = 'F3A3C0C130549300E6A42B7F0E2B7F60';
    // Realiza la llamada a la API de iplocation.net para obtener los datos de ubicación por IP
    $api_url = "https://api.ip2location.io/?key=$token&ip=$ip";
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);
    
    if (isset($data['city_name']) && !empty($data['city_name'])) {
        $location['city'] = $data['city_name'];
    }

    if (isset($data['country_name']) && !empty($data['country_name'])) {
        $location['country'] = $data['country_name'];
    }

    return $location;
}

    private function getWeatherData($location)
    {
        
        $api_key = '3778d8f17b7f119adee8f623ec7d3029';
        $api_url = "http://api.openweathermap.org/data/2.5/weather?q={$location['city']}&appid={$api_key}&units=metric";

        $response = file_get_contents($api_url);
        $data = json_decode($response, true);

        $weather_data = array(
            'location' => $location['city'] . ', ' . $location['country'],
            'temperature' => $data['main']['temp'] . '°C',
            'humidity' => $data['main']['humidity'] . '%',
            'icon' => $data['weather'][0]['icon'],
        );

        return $weather_data;
    }
}