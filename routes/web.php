<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceManagementController;
use App\Http\Controllers\DeviceMangementController;
use App\Http\Controllers\DeviceTimeController;
use App\Http\Controllers\LongPollingController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
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

// Public routes that do not require authentication
Route::get('/', function () {
    return view('login');
})->name('login');

Route::get('/login', function () {
    return view('login');
})->name('login');

// PUBLIC APIs - These routes are accessible without authentication
Route::post('/api/device/insert', [DeviceMangementController::class, 'InsertDeviceDetails'])->name('device.insert');
Route::post('/api/device/update', [DeviceMangementController::class, 'UpdateDeviceDetails'])->name('device.update');
Route::delete('/api/device/{id}/delete', [DeviceMangementController::class, 'DeleteDevice'])->name('device.delete');
Route::get('/api/device/{id}/test', [DeviceMangementController::class, 'DeviceTest'])->name('device.test');
Route::post('/api/device-time/end', [DeviceTimeController::class, 'EndDeviceTimeAPI'])->name('device-time.api.end');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // DEVICE CONTROLLERS
    Route::get('/device', [DeviceMangementController::class, 'GetDevices'])->name('devicemanagement');
    Route::get('/device/{id}', [DeviceMangementController::class, 'GetDeviceDetails'])->name('device.detail');
    Route::post('/device/{id}/deploy', [DeviceMangementController::class, 'UpdateDeviceOperationDate'])->name('device.deploy');
    Route::post('/device/{id}/disable', [DeviceMangementController::class, 'DisableDevice'])->name('device.disable');
    Route::post('/device/{id}/enable', [DeviceMangementController::class, 'EnableDevice'])->name('device.enable');
    Route::post('/device/update/name', [DeviceMangementController::class, 'UpdateDeviceName'])->name('device.update.devicename');
    Route::post('/device/update/watchdog', [DeviceMangementController::class, 'UpdateWatchdogInterval'])->name('device.update.watchdog');
    Route::post('/device/update/remainingtime', [DeviceMangementController::class, 'UpdateRemainingTimeNotification'])->name('device.update.remainingtime');

    // TIME CONTROLLERS
    Route::post('/device-time/increment', [DeviceTimeController::class, 'InsertDeviceIncrement'])->name('device-time.increment.insert');
    Route::post('/device-time/increment/update/{id}', [DeviceTimeController::class, 'UpdateDeviceIncrement'])->name('device-time.increment.update');
    Route::delete('/device-time/increment/delete/{id}', [DeviceTimeController::class, 'DeleteDeviceIncrement'])->name('device-time.increment.delete');
    Route::post('/device-time/increment/status/{id}', [DeviceTimeController::class, 'UpdateDeviceIncrementStatus'])->name('device-time.increment.status');

    Route::post('/device-time/base', [DeviceTimeController::class, 'InsertDeviceBase'])->name('device-time.base');
    Route::post('/device-time/start/{id}', [DeviceTimeController::class, 'StartDeviceTime'])->name('device-time.start');
    Route::post('/device-time/end/{id}', [DeviceTimeController::class, 'EndDeviceTime'])->name('device-time.end');
    Route::post('/device-time/pause/{id}', [DeviceTimeController::class, 'PauseDeviceTime'])->name('device-time.pause');
    Route::post('/device-time/resume/{id}', [DeviceTimeController::class, 'ResumeDeviceTime'])->name('device-time.resume');
    Route::post('/device-time/extend/{id}', [DeviceTimeController::class, 'ExtendDeviceTime'])->name('device-time.extend');
    Route::post('/device/{id}/free', [DeviceTimeController::class, 'FreeLight'])->name('device.free');
    Route::post('/device/{id}/stopfree', [DeviceTimeController::class, 'StopFreeLight'])->name('device.stop.free');

    Route::get('/reports', function () {
        return view('reports');
    })->name('reports');

    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');

    // ROLES
    Route::get('/manage-roles', [RoleController::class, 'GetRoles'])->name('manage-roles');
    Route::get('/role/{roleId}', [RoleController::class, 'GetRole'])->name('role');
    Route::post('/role/insert', [RoleController::class, 'InsertRole'])->name('roles.insert');
    Route::delete('/role/delete/{roleId}', [RoleController::class, 'DeleteRole'])->name('roles.delete');
    Route::post('/roles/{roleId}/update', [RoleController::class, 'UpdateRole'])->name('roles.update');

    // USERS
    Route::get('/manage-users', [UserController::class, 'GetUsers'])->name('manage-users');
    Route::get('/user/{userId}', [UserController::class, 'GetUser'])->name('user');
    Route::get('/profile/{userId}', [UserController::class, 'GetUserProfile'])->name('profile');
    Route::post('/user/insert', [UserController::class, 'InsertUser'])->name('user.insert');
    Route::post('/user/{userId}/update', [UserController::class, 'UpdateUser'])->name('user.update');
    Route::post('/profile/{userId}/update', [UserController::class, 'UpdateUserProfile'])->name('profile.update');
    Route::delete('/user/delete/{userId}', [UserController::class, 'DeleteUser'])->name('user.delete');
    Route::post('/user/status/{userId}/{status}', [UserController::class, 'UserStatus'])->name('user.status');

    // REPORTS
    Route::get('/reports/finance', [ReportsController::class, 'GetFinanceReports'])->name('reports.finance');
    Route::get('/reports/device/usage/daily/{id}', [ReportsController::class, 'GetDailyUsage']);
    Route::get('/reports/device/usage/monthly/{id}', [ReportsController::class, 'GetMonthlyUsage']);
    Route::get('/reports/transactions/filter', [ReportsController::class, 'GetFilteredTransactions'])->name('reports.transactions.filter');

    // LONG POLLING
    Route::get('/active-transactions', [LongPollingController::class, 'GetActiveTransactions']);
});

// AUTH
Route::post('/login', [AuthController::class, 'UserLogin'])->name('auth.login');
Route::post('/logout', [AuthController::class, 'UserLogout'])->name('auth.logout');
