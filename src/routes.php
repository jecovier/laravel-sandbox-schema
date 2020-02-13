<?php

Route::get('sandbox/{schema}', SandboxController::class . '@index')
    ->where('schema', '.*');
Route::post('sandbox/{schema}', SandboxController::class . '@store')
    ->where('schema', '.*');
Route::put('sandbox/{schema}', SandboxController::class . '@update')
    ->where('schema', '.*');
Route::delete('sandbox/{schema}', SandboxController::class . '@destroy')
    ->where('schema', '.*');

Route::apiResource('sandbox/schema', SchemaController::class);
