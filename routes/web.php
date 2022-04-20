<?php

use App\Http\Controllers\Auth\LoginController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('login', [LoginController::class, 'create'])->name('login');

Route::post('login', [LoginController::class, 'store'])->name('store');

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');


Route::middleware('auth')->group(function (){

    Route::get('/', function () {
        return inertia('Home');
    });

    Route::get('/users', function () {
        return inertia('Users/Index', [
            'users' => User::query()
                ->when(Request::input('search'), function ($query, $search) {
                    $query->where('name', 'like', '%' . $search . '%' );
                })
                ->paginate(10)
                ->withQueryString()
                ->through(
                    function ($user) {
                        return [
                            'name' => $user->name,
                            'id' => $user->id,
                            'can' => [
                                'edit' => Auth::user()->can('edit', $user)
                            ]

                        ];
                    }
                ),
            'filters' => Request::only(['search']),
            'can' => [
                'createUser' => Auth::user()->can('create', User::class)
            ]
        ]);
    });

    Route::get('/users/create', function (){
        return inertia('Users/Create');
    })->can('create','App\Models\User');

    Route::post('/users', function (){
        // Validate request
        $attrs = Request::validate([
            'name' => 'required',
            'email' => ['required', 'email'],
            'password' => 'required'
        ]);

        // Create user
        User::create($attrs);

        // Redirect
        return redirect('/users');


    });


    Route::get('/settings', function () {

        return inertia('Settings');
    });


});
