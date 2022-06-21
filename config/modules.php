<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Namespace
    |--------------------------------------------------------------------------
    |
    | Default module namespace.
    |
    */

    'namespace' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | Module Stubs
    |--------------------------------------------------------------------------
    |
    | Default module stubs.
    |
    */

    'stubs'      => [
        'files'        => [
            'routes/web'      => 'Routes/web.php',
            'routes/api'      => 'Routes/api.php',
            'views/index'     => 'Resources/views/index.blade.php',
            'views/master'    => 'Resources/views/layouts/master.blade.php',
            'scaffold/config' => 'Config/config.php',
        ],
        'replacements' => [
            'routes/web'      => ['LOWER_NAME', 'STUDLY_NAME'],
            'routes/api'      => ['LOWER_NAME'],
            'webpack'         => ['LOWER_NAME'],
            'json'            => ['LOWER_NAME', 'STUDLY_NAME', 'MODULE_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/index'     => ['LOWER_NAME'],
            'views/master'    => ['LOWER_NAME', 'STUDLY_NAME'],
            'scaffold/config' => ['STUDLY_NAME'],
            'composer'        => [
                'LOWER_NAME',
                'STUDLY_NAME',
                'VENDOR',
                'AUTHOR_NAME',
                'AUTHOR_EMAIL',
                'MODULE_NAMESPACE',
                'PROVIDER_NAMESPACE',
            ],
        ],
        'gitkeep'      => false,
    ],
    'paths'      => [
        /*
        |--------------------------------------------------------------------------
        | Modules path
        |--------------------------------------------------------------------------
        |
        | This path used for save the generated module. This path also will be added
        | automatically to list of scanned folders.
        |
        */

        'modules' => base_path('Modules'),
        /*
        |--------------------------------------------------------------------------
        | Modules assets path
        |--------------------------------------------------------------------------
        |
        | Here you may update the modules assets path.
        |
        */

        'assets'    => public_path('modules'),

        /*
        |--------------------------------------------------------------------------
        | Generator path
        |--------------------------------------------------------------------------
        | Customise the paths where the folders will be generated.
        | Set the generate key to false to not generate that folder
        */
        'generator' => [
            'config'          => ['path' => 'Config', 'generate' => true],
            'command'         => ['path' => 'Console', 'generate' => false],
            'migration'       => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder'          => ['path' => 'Database/Seeders', 'generate' => true],
            'factory'         => ['path' => 'Database/factories', 'generate' => false],
            'model'           => ['path' => 'Entities', 'generate' => true],
            'routes'          => ['path' => 'Routes', 'generate' => true],
            'controller'      => ['path' => 'Http/Controllers/Web', 'generate' => true],
            'filter'          => ['path' => 'Http/Middleware', 'generate' => true],
            'request'         => ['path' => 'Http/Requests', 'generate' => true],
            'provider'        => ['path' => 'Providers', 'generate' => false],
            'assets'          => ['path' => 'Resources/assets', 'generate' => false],
            'lang'            => ['path' => 'Resources/lang', 'generate' => false],
            'views'           => ['path' => 'Resources/views', 'generate' => true],
            'test'            => ['path' => 'Tests/Unit', 'generate' => false],
            'test-feature'    => ['path' => 'Tests/Feature', 'generate' => true],
            'repository'      => ['path' => 'Repositories', 'generate' => true],
            'event'           => ['path' => 'Events', 'generate' => false],
            'listener'        => ['path' => 'Listeners', 'generate' => false],
            'policies'        => ['path' => 'Policies', 'generate' => false],
            'rules'           => ['path' => 'Rules', 'generate' => false],
            'jobs'            => ['path' => 'Jobs', 'generate' => false],
            'emails'          => ['path' => 'Emails', 'generate' => false],
            'notifications'   => ['path' => 'Notifications', 'generate' => false],
            'resource'        => ['path' => 'Transformers', 'generate' => false],
            'component-view'  => ['path' => 'Resources/views/components', 'generate' => false],
            'component-class' => ['path' => 'View/Components', 'generate' => false],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | Here is the config for setting up caching feature.
    |
    */
    'cache'      => [
        'enabled'  => false,
        'key'      => 'laravel-modules',
        'lifetime' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Activators
    |--------------------------------------------------------------------------
    |
    | You can define new types of activators here, file, database etc. The only
    | required parameter is 'class'.
    | The file activator will store the activation status in storage/installed_modules
    */
    'activators' => [
        'file' => [
            'cache-key'      => 'activator.installed',
            'cache-lifetime' => 604800,
        ],
    ],
];
