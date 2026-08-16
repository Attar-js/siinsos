<?php

use App\Http\Controllers\Admin\NilaiAkhirController;
use Illuminate\Support\Facades\Route;

Route::middleware('apikey')->group(function () {
    Route::get('/nilai-akhir', [NilaiAkhirController::class, 'apiIndex'])->name('api.nilai-akhir.index');
    Route::get('/nilai-akhir/{nim}', [NilaiAkhirController::class, 'apiShow'])->name('api.nilai-akhir.show');
});
