<?php

Route::get('api/sandbox/{schema}', \Jecovier\SandboxSchema\SandboxController::class . '@index')
    ->where('schema', '.*');
Route::post('api/sandbox/{schema}', \Jecovier\SandboxSchema\SandboxController::class . '@store')
    ->where('schema', '.*');
Route::put('api/sandbox/{schema}', \Jecovier\SandboxSchema\SandboxController::class . '@update')
    ->where('schema', '.*');
Route::delete('api/sandbox/{schema}', \Jecovier\SandboxSchema\SandboxController::class . '@destroy')
    ->where('schema', '.*');

Route::apiResource('api/sandbox/schema', \Jecovier\SandboxSchema\SchemaController::class);
