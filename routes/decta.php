<?php

Route::any( '/merchant/decta/webhook_success', 'Dnsoftware\Decta\Controllers\DectaController@decta_webhook_success' );
Route::any( '/merchant/decta/webhook_failure', 'Dnsoftware\Decta\Controllers\DectaController@decta_webhook_failure' );

Route::any( '/merchant/decta/return_success', 'Dnsoftware\Decta\Controllers\DectaController@decta_return_success' );
Route::any( '/merchant/decta/return_failure', 'Dnsoftware\Decta\Controllers\DectaController@decta_return_failure' );
