<?php

use App\Models\ATC\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ATIS Routes
Route::get('/atis/{atis_letter}/{deprwy}/{arrrwy}/{app}/{dep}', 'ATC\AtisController@Index');
Route::get('/atisurl', function() {
  return redirect()->route('app.atc.tools', 'fr');
});

// CoFrance Routes
Route::group(['prefix' => '/cfr'], function() {
  Route::get('/', function(Request $request) {
    return response()->json([
      'response' => 'Success',
      'message' => 'CoFrance API alpha 1.0',
    ]);
  });
  Route::get('/test', 'CoFrance\CoFranceController@test');
  Route::get('/config', 'CoFrance\CoFranceController@sendConfig');
  Route::group(['middleware' => 'COFRANCEAPI'], function() {
    Route::get('/checktoken', 'CoFrance\CoFranceController@checkToken');
  });

  Route::group(['prefix' => '/stand'], function() {
    Route::get('/', 'CoFrance\StandApiController@active');
    Route::post('/query', 'CoFrance\StandApiController@query');
  });

  Route::group(['prefix' => '/ssr'], function() {
    Route::get('/', 'CoFrance\SSRApiController@query');
  });
});

// vOPS Routes
Route::group(['prefix' => '/vops'], function() {
  Route::get('/bookings', function() {
    $bookings = Booking::whereDate('start_date', '>=', Carbon::now())
    ->with('user')
    ->with(['user' => function($query) {
      return $query->select('vatsim_id', 'fname', 'lname', 'atc_rating', 'atc_rating_short', 'atc_rating_long');
    }])
    ->orderBy('start_date', 'ASC')
    ->get();
    
    return response([
      "bookings" => $bookings
    ], 200);
  });
});