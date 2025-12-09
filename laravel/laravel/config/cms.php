<?php
return [
    'cms_url' => env('CMS_URL', 'http://barhoom.local/api/'),
    'api_key' => env('LICENSE_SERVER_API_KEY', ''),
    'generate_token' => env('CMS_URL', 'http://barhoom.local/api/') . 'generate-token',
    'get_token' => env('CMS_URL', 'http://barhoom.local/api/') . 'get-token-record',
    'create_new_token' => env('CMS_URL', 'http://barhoom.local/api/') . 'create-new-token',
    'save_project' => env('CMS_URL', 'http://barhoom.local/api/') . 'save-project',
];
