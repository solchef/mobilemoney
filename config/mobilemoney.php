<?php

return [
    //
    'c2b' => [
        /*
         * Configure the minimum amount
         */
        'mimimum_amount'=>50,
        /*
         * Consumer Key from developer portal
         */
        'consumer_key' => 'Ei4lr5xbDZXS9XEAZ1BhNE4xCBcAYGVy',
        /*
         * Consumer secret from developer portal
         */
        'consumer_secret' => 'eMhCDmzFQyx1SNSZ',
        /*
         * HTTP callback method [POST,GET]
         */
        'callback_method' => 'POST',
        /*
         * Your receiving paybill or till umber
         */
        'short_code' => 600152,
        /*
         * Passkey , requested from mpesa
         */
        'passkey' => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919',
        /*
         * --------------------------------------------------------------------------------------
         * Callbacks:
         * ---------------------------------------------------------------------------------------
         * Please update your app url in .env file
         * Note: This package has already routes for handling this callback.
         * You should leave this values as they are unless you know what you are doing
         */
        /*
         * Stk callback URL
         */
        'stk_callback' => env('APP_URL') . '/stk_callback',
        /*
         * Data is sent to this URL for successful payment
         */
        'confirmation_url' => env('APP_URL') . '/confirmation',
        /*
         * MobileMoney validation URL.
         * NOTE: You need to email MPESA to enable validation
         */
        'validation_url' => env('APP_URL') . '/validate',
    ],
    'b2c' => [
        /*
         * Sending app consumer key
         */
        'consumer_key' => 'Ei4lr5xbDZXS9XEAZ1BhNE4xCBcAYGVyA',
        /*
         * Sending app consumer secret
         */
        'consumer_secret' => 'eMhCDmzFQyx1SNSZ',
        /*
         * Shortcode sending funds
         */
        'short_code' => 600000,
        /*
        * This is the user initiating the transaction, usually from the MobileMoney organization portal
        * Make sure this was the user who was used to 'GO LIVE'
        * https://org.ke.m-pesa.com/
        */
        'initiator' => 'testapi',
        /*
         * The user security credential.
         * Go to https://developer.safaricom.co.ke/test_credentials and paste your initiator password to generate
         * security credential
         */
        'security_credential' => 'GXiVXirQFaJvEFOQyn+VJ4Gp3Ccvpoq6aqzFiNgvH18UMU59Qxc+UTAX7Blzo6L0+tQG2wUJ1fKH4YlPagtzDHT37796uu0NysS85uPjxZMjnbGhPNeHnhJLzwyrjppl8mZpnmVg4CaVrEdcriuyifKIiF1hmc0A/RnjBMzY6yevbIV0kAgrn5cDvCN99O1rr1nl69GaVbP7a/6AWnRkVUldnalQmqQhfgLbOdxjGOVGU2arqjuvgQ6glo1uK9PUnp3UH2Vv66Lu99JglWyjlcWufZhJXUmFFB9tfoKAX2URnPGi4PvvJ6OgJNdsJmTsevnG2c/KKOa45rzdvwrwKA==',
        /*
         * Notification URL for timeout
         */
        'timeout_url' => env('APP_URL') . '/payments/callbacks/timeout/',
        /**
         * Result URL
         */
        'result_url' => env('APP_URL') . '/payments/callbacks/result/',
    ],
    'airtelm'=>[
        'url' => 'https://airtelmoneymq.ke.airtel.com:8446/MerchantQueryService.asmx?WSDL',
        'msisdn' =>'254000000504',
        'password' =>'mtransf123$',
        'username' => '405405user',
    ],
];