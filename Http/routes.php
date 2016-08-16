<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/



Route::any('test', function (){
    return view('test');
});

Route::auth();
Route::get('/login', 'Auth\AuthController@getLogin');
Route::post('/login', 'Auth\AuthController@postLogin');
Route::get('/logout', 'Auth\AuthController@logout');


Route::get('/', ['middleware' => 'guest', function()
{
    return view('welcome');
}]);




// Registration routes...

// Password Reset Routes...
$this->get('password/reset/{token?}', 'Auth\PasswordController@showResetForm');
$this->post('password/email', 'Auth\PasswordController@sendResetLinkEmail');
$this->post('password/reset', 'Auth\PasswordController@reset');



Route::group(['middleware' => 'auth'], function () {

    Route::group(['middleware' => 'admin'], function () {

        Route::any('admin/festival/upload-data', 'FestivalsController@importCSV');
        Route::get('/admin-login', function () {
            return view('admin/dashboard/index');
        });
        Route::get('admin/dashboard', function () {
            return view('admin/dashboard/index');
        });
        Route::get('/admin/user', function () {
            return view('admin/masters/user/show');
        });
        Route::get('/admin/user', function () {
            return view('admin/masters/user/show');
        });
        /* start festival */
        Route::any('admin/festival/data', 'FestivalsController@dataFestival');
        Route::resource('/admin/festival', 'FestivalsController');
        /*end festival */

        /* start time Zone*/
        Route::any('timezone/data', 'TimezoneController@anyData');
        Route::resource('admin/timezone', 'TimezoneController');
        /* end time Zone*/

        /* start calendarType*/
        Route::any('calendarType/data', 'CalendarTypeController@anyData');
        Route::resource('admin/calendarType', 'CalendarTypeController');
        /* end calendarType*/

        /* start merchandiseType*/
        Route::any('merchandiseType/data', 'MerchandiseTypeController@anyData');
        Route::resource('admin/merchandiseType', 'MerchandiseTypeController');
        /* end merchandiseType*/

        /* start paytype*/
        Route::any('paytype/data', 'PaytypeController@anyData');
        Route::resource('admin/paytype', 'PaytypeController');
        /* end paytype*/

        /* start supplierType*/
        Route::any('supplierType/data', 'SupplierTypeController@anyData');
        Route::resource('admin/supplierType', 'SupplierTypeController');
        /* end supplierType*/

        Route::any('subscription/data', 'SubscriptionController@anyData');
        Route::resource('admin/subscription', 'SubscriptionController');

        Route::any('admin/promotional/data', 'PromotionalCotroller@anyData');
        Route::resource('admin/promotional', 'PromotionalCotroller');


    });
    /*-----------------------------SETUP ROUTE -----------------------------------------*/
    Route::get('renewal', 'SubscriptionController@getRenewal');
    Route::get('payment/{id}', 'PromoCodeController@getSubscription');
    /*Route::any('payment', function (){
        return view('master.subscripton.promo-code-payment');
    });*/
    /*-----------------------------SETUP ROUTE -----------------------------------------*/
    Route::group(['middleware' => 'setup'], function () {
        Route::get('/register', 'Auth\AuthController@getRegister');
        Route::post('/register', 'Auth\AuthController@postRegister');
        Route::get('/home', 'HomeController@index');

        Route::controller('home', 'HomeController', [
            'anyData'  => 'home.data',
            'index' => 'home',
        ]);
        Route::any('festival/genre', 'FestivalsController@getAllStateAndGenre');
        Route::get('/task/create/{objectId}/{objectNameId}', 'TaskController@createTaskByObjectNameAndId');
        Route::post('/task/home/objectName/{objectId}', 'TaskController@getObjectNameByObjectId');
        Route::any('/task/home/data', 'TaskController@anyData');
        Route::post('/task/updateTaskState', 'TaskController@updateTaskState');

        Route::any('landing', function (){
            return view('master.Landing');
        });
        Route::any('band/data', 'BandController@anyData');
        Route::any('band/activeBand', 'BandController@activeBand');
        Route::resource('/band', 'BandController');

        Route::any('setup/data', 'SetupController@anyData');
        Route::resource('/setup', 'SetupController');




         Route::group(['middleware' => 'activeBand'], function () {

            Route::any('contact/work/{work}', 'ContactController@getWorkFor');
            Route::any('contact/data', 'ContactController@anyData');
            Route::resource('/contact', 'ContactController');

            Route::any('venue/data', 'VanuesController@anyData');
            Route::resource('/venue', 'VanuesController');

            Route::any('task/data', 'TaskController@getData');
            Route::resource('/task', 'TaskController');

            Route::any('calendar/data', 'CalenderController@anyData');
            Route::resource('/calendar', 'CalenderController');

            Route::any('agent/data', 'AgentController@anyData');
            Route::resource('/agent', 'AgentController');

            Route::any('merchandis/data', 'MerchandiseController@anyData');
            Route::resource('/merchandis', 'MerchandiseController');

            Route::any('press/data', 'PressController@anyData');
            Route::resource('/press', 'PressController');

            Route::any('supplier/data', 'SuppliersController@anyData');
            Route::resource('/supplier', 'SuppliersController');

            Route::any('track/data', 'TrackController@anyData');
            Route::resource('/track', 'TrackController');


            Route::any('gig/location/{lovation}', 'GigController@getLocation');
            Route::any('gig/data', 'GigController@anyData');
            Route::resource('/gig', 'GigController');



            Route::get('subscription', 'SubscriptionController@getSubscription');
        });

        /*--------------------- start festival url --------------------------*/
        Route::get('festival/data', 'FestivalsController@anyData');
        Route::get('festival', 'FestivalsController@getFestival');
        Route::get('festival/show/{id}', 'FestivalsController@showFestival');
        /*--------------------- start contact url --------------------------*/
        Route::any('contact/work/{work}', 'ContactController@getWorkFor');
        Route::any('contact/data', 'ContactController@anyData');
        Route::resource('/contact', 'ContactController', ['only' => [
            'index', 'show','destroy'
        ]]);
        /*----------------------venue url ----------------------------------*/
        Route::any('venue/data', 'VanuesController@anyData');
        Route::resource('/venue', 'VanuesController', ['only' => [
            'index', 'show','destroy'
        ]]);
        /*----------------------task url ----------------------------------*/
        Route::any('agent/data', 'AgentController@anyData');
        Route::resource('/agent', 'AgentController', ['only' => [
            'index', 'show','destroy'
        ]]);

        Route::any('merchandis/data', 'MerchandiseController@anyData');
        Route::resource('/merchandis', 'MerchandiseController', ['only' => [
            'index', 'show','destroy'
        ]]);

        Route::any('press/data', 'PressController@anyData');
        Route::resource('/press', 'PressController', ['only' => [
            'index', 'show','destroy'
        ]]);

        Route::any('supplier/data', 'SuppliersController@anyData');
        Route::resource('/supplier', 'SuppliersController', ['only' => [
            'index', 'show','destroy'
        ]]);

        Route::any('track/data', 'TrackController@anyData');
        Route::resource('/track', 'TrackController', ['only' => [
            'index', 'show','destroy'
        ]]);
    });
});



// verification token resend form
Route::get('verify/resend', [
    'uses' => 'Auth\VerifyController@showResendForm',
    'as' => 'verification.resend',
]);

// verification token resend action
Route::post('verify/resend', [
    'uses' => 'Auth\VerifyController@sendVerificationLinkEmail',
    'as' => 'verification.resend.post',
]);

// verification message / user verification
Route::get('verify/{token?}', [
    'uses' => 'Auth\VerifyController@verify',
    'as' => 'verification.verify',
]);