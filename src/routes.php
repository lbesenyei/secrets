<?php

Route::get('secret/{hash}', 'Lbesenyei\Secrets\SecretsController@show');
Route::post('secret', 'Lbesenyei\Secrets\SecretsController@store');
