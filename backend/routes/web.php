<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DirektoriUsahaController;

Route::get('/test-direktori-usaha', function () {
    $controller = new DirektoriUsahaController();
    $request = new \Illuminate\Http\Request();
    $request->merge(['per_page' => 5]);
    
    $response = $controller->index($request);
    
    echo "Testing Direktori Usaha API:\n";
    echo "Status Code: " . $response->status() . "\n";
    echo "Content:\n";
    print_r(json_decode($response->content(), true));
});

Route::get('/', function () {
    return redirect('/test-direktori-usaha');
});
