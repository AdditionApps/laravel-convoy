<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Convoy database table name
    |--------------------------------------------------------------------------
    |
    | Here you can specify which driver you want to use to track and persist
    | convoy data.  You should ensure that your application is configured
    | correctly to support the convoy driver that you wish to employ
    |
    | Supported Drivers: "database"
    |
    */

    'driver' => env('CONVOY_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Convoy database table name
    |--------------------------------------------------------------------------
    |
    | If you would like to use the database driver for tracking convoys in your
    | application you can customise the table name the package references if
    | the default name clashes with an existing table in your application
    */

    'database_name' => env('CONVOY_DATABASE_TABLE', 'convoys'),

    /*
    |--------------------------------------------------------------------------
    | Convoy database connection name
    |--------------------------------------------------------------------------
    |
    | In the event that you are using the database driver for tracking convoys
    | and you wish to run the database migration for the convoy table on some
    | non-default connection then you can specify that connection name here
    */

    'database_connection' => env(
        'CONVOY_DATABASE_CONNECTION', config('database.default')
    ),

];
