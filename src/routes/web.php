<?php


Route::group(['prefix' => 'mpesa/',
    'namespace' => 'Jawiwy\MobileMoney\src\Mpesa\Http\Controllers'
], function () {
    Route::get('mobilemoney', 'MpesaController@index');
    Route::any('validate', 'MpesaController@validatepayment');
    Route::any('confirmation', 'MpesaController@confirmation');
    Route::any('callback', 'MpesaController@callback');
    Route::any('balance_b2c', 'MpesaController@balance_b2c');
    Route::any('balance_c2b', 'MpesaController@balance_c2b');
    Route::any('b2btransfer', 'MpesaController@b2btransfer');
    Route::any('b2breversal', 'MpesaController@b2breversal');
    Route::any('b2b_status/{id}', 'MpesaController@b2b_status');
    Route::any('stk_callback', 'MpesaController@stkcallback');
    Route::any('initiateb2c', 'MpesaController@initiateb2c');
    Route::any('timeout/{section?}', 'MpesaController@queuetimeout');
    Route::any('result/{section?}', 'MpesaController@result');
    Route::any('stk_request', 'StkController@initiatePush');
    Route::any('generatetoken', 'StkController@generatetoken');
    Route::get('stk_status/{id}', 'StkController@stkStatus');
});