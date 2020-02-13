<?php

Route::get('api/sandbox/{schema}', SandboxController::class . '@index')
    ->where('schema', '.*');
Route::post('api/sandbox/{schema}', SandboxController::class . '@store')
    ->where('schema', '.*');
Route::put('api/sandbox/{schema}', SandboxController::class . '@update')
    ->where('schema', '.*');
Route::delete('api/sandbox/{schema}', SandboxController::class . '@destroy')
    ->where('schema', '.*');

Route::apiResource('api/sandbox/schema', SchemaController::class);
