<?php return array (
  'backpack/basset' => 
  array (
    'aliases' => 
    array (
      'Basset' => 'Backpack\\Basset\\Facades\\Basset',
    ),
    'providers' => 
    array (
      0 => 'Backpack\\Basset\\BassetServiceProvider',
    ),
  ),
  'backpack/crud' => 
  array (
    'aliases' => 
    array (
      'CRUD' => 'Backpack\\CRUD\\app\\Library\\CrudPanel\\CrudPanelFacade',
      'Widget' => 'Backpack\\CRUD\\app\\Library\\Widget',
    ),
    'providers' => 
    array (
      0 => 'Backpack\\CRUD\\BackpackServiceProvider',
    ),
  ),
  'backpack/theme-tabler' => 
  array (
    'providers' => 
    array (
      0 => 'Backpack\\ThemeTabler\\AddonServiceProvider',
    ),
  ),
  'creativeorange/gravatar' => 
  array (
    'aliases' => 
    array (
      'Gravatar' => 'Creativeorange\\Gravatar\\Facades\\Gravatar',
    ),
    'providers' => 
    array (
      0 => 'Creativeorange\\Gravatar\\GravatarServiceProvider',
    ),
  ),
  'laravel/sanctum' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Sanctum\\SanctumServiceProvider',
    ),
  ),
  'laravel/tinker' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Tinker\\TinkerServiceProvider',
    ),
  ),
  'nesbot/carbon' => 
  array (
    'providers' => 
    array (
      0 => 'Carbon\\Laravel\\ServiceProvider',
    ),
  ),
  'nunomaduro/termwind' => 
  array (
    'providers' => 
    array (
      0 => 'Termwind\\Laravel\\TermwindServiceProvider',
    ),
  ),
  'php-mqtt/laravel-client' => 
  array (
    'aliases' => 
    array (
      'MQTT' => 'PhpMqtt\\Client\\Facades\\MQTT',
    ),
    'providers' => 
    array (
      0 => 'PhpMqtt\\Client\\MqttClientServiceProvider',
    ),
  ),
  'prologue/alerts' => 
  array (
    'aliases' => 
    array (
      'Alert' => 'Prologue\\Alerts\\Facades\\Alert',
    ),
    'providers' => 
    array (
      0 => 'Prologue\\Alerts\\AlertsServiceProvider',
    ),
  ),
);