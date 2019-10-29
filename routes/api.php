<?php
if(env('APP_ENV')=="local" || env('APP_ENV')==null){
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: *');
    header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );    
}

use Illuminate\Http\Request;

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

Route::post('login', 'AuthController@login');
Route::post('register', 'AuthController@register');
Route::get('verify/{token}', 'AuthController@verify');
Route::get('password/reset', 'AuthController@reset');
Route::post('password/reset/{token}', 'AuthController@change');
Route::get('users', 'UserController@findByToken')->middleware('api');
Route::post('users', 'UserController@store')->middleware('api');
Route::put('users/{id}', 'UserController@update')->where('id', '[0-9]+')->middleware('api');
Route::get('users/{id}', 'UserController@show')->where('id', '[0-9]+')->middleware('api');
Route::delete('users/{id}', 'UserController@destroy')->where('id', '[0-9]+')->middleware('api');
Route::post('users/findUsers', 'UserController@findUsers')->middleware('api');
Route::get('users/list', 'UserController@list');
Route::get('permissions', 'PermissionController@index')->middleware('api');

Route::get('employees', 'EmployeeController@index')->middleware('api');
Route::post('employees', 'EmployeeController@store');
Route::get('employees/{id}', 'EmployeeController@show')->where('id', '[0-9]+')->middleware('api');
Route::post('employees/{id}', 'EmployeeController@update')->where('id', '[0-9]+')->middleware('api');
Route::post('employees/{id}/uploadDocument', 'EmployeeController@updateDocument')->where('id', '[0-9]+')->middleware('api');
Route::put('employees/{id}/disable', 'EmployeeController@disable')->where('id', '[0-9]+')->middleware('api');
Route::put('employees/{id}/restore', 'EmployeeController@restore')->where('id', '[0-9]+')->middleware('api');
Route::put('employees/{id}/deleteDocument', 'EmployeeController@deleteDocument')->where('id', '[0-9]+')->middleware('api');
Route::put('employees/updateUser', 'EmployeeController@updateUser')->where('id', '[0-9]+')->middleware('api');
Route::put('employees/updateBank', 'EmployeeController@updateBank')->where('id', '[0-9]+')->middleware('api');
Route::post('employees/convert', 'EmployeeController@convert')->middleware('api');
Route::post('employees/reject', 'EmployeeController@reject')->middleware('api');
Route::get('employees/unhired', 'EmployeeController@unhired')->middleware('api');
Route::get('employees/confirmed', 'EmployeeController@confirmed')->middleware('api');
Route::get('employees/mail-test', 'EmployeeController@mailTest')->middleware('api');

Route::get('roles', 'RoleController@index')->middleware('api');
Route::put('roles', 'RoleController@update')->middleware('api');
Route::post('roles/findRoles', 'RoleController@findRoles')->middleware('api');

Route::get('attributes', 'AttributeController@index')->middleware('api');
Route::post('attributes', 'AttributeController@create')->middleware('api');
Route::get('attributes/{id}', 'AttributeController@show')->where('id', '[0-9]+')->middleware('api');
Route::put('attributes/{id}', 'AttributeController@update')->where('id', '[0-9]+')->middleware('api');
Route::get('attributes/{id}/updateStatus', 'AttributeController@updateStatus')->where('id', '[0-9]+')->middleware('api');
Route::get('departments', 'DepartmentController@index')->middleware('api');
Route::post('departments', 'DepartmentController@create')->middleware('api');
Route::get('departments/{id}', 'DepartmentController@show')->where('id', '[0-9]+')->middleware('api');
Route::put('departments/{id}', 'DepartmentController@update')->where('id', '[0-9]+')->middleware('api');
Route::get('departments/{id}/updateStatus', 'DepartmentController@updateStatus')->where('id', '[0-9]+')->middleware('api');
Route::get('companies', 'CompanyController@index')->middleware('api');
Route::post('companies', 'CompanyController@create')->middleware('api');
Route::get('companies/{id}', 'CompanyController@show')->where('id', '[0-9]+')->middleware('api');
Route::put('companies/{id}', 'CompanyController@update')->where('id', '[0-9]+')->middleware('api');
Route::get('companies/{id}/updateStatus', 'CompanyController@updateStatus')->where('id', '[0-9]+')->middleware('api');
Route::get('companies/list', 'CompanyController@list')->middleware('api');
Route::post('companies/{id}/uploadHours', 'CompanyController@uploadHours')->where('id', '[0-9]+')->middleware('api');
Route::put('companies/{id}/deleteHours', 'CompanyController@deleteHours')->where('id', '[0-9]+')->middleware('api');
Route::get('companies/worksnap/{id}', 'CompanyController@worksnap')->where('id', '[0-9]+')->middleware('api');
Route::get('contracts', 'ContractController@index')->middleware('api');
Route::post('contracts', 'ContractController@store')->middleware('api');
Route::get('contracts/{id}', 'ContractController@show')->where('id', '[0-9]+')->middleware('api');
Route::put('contracts/{id}', 'ContractController@update')->where('id', '[0-9]+')->middleware('api');
Route::get('contracts/{id}/updateStatus', 'ContractController@updateStatus')->where('id', '[0-9]+')->middleware('api');
Route::get('companyUsers', 'CompanyUserController@index')->middleware('api');
Route::post('companyUsers', 'CompanyUserController@store')->middleware('api');
Route::get('companyUsers/{id}', 'CompanyUserController@show')->where('id', '[0-9]+')->middleware('api');
Route::put('companyUsers/{id}', 'CompanyUserController@update')->where('id', '[0-9]+')->middleware('api');
Route::get('companyUsers/{id}/updateStatus', 'CompanyUserController@updateStatus')->where('id', '[0-9]+')->middleware('api');
Route::get('jobs', 'JobController@index')->middleware('api');
Route::post('jobs', 'JobController@create')->middleware('api');
Route::get('jobs/{id}', 'JobController@show')->where('id', '[0-9]+')->middleware('api');
Route::put('jobs/{id}', 'JobController@update')->where('id', '[0-9]+')->middleware('api');
Route::get('jobs/{id}/updateStatus', 'JobController@updateStatus')->where('id', '[0-9]+')->middleware('api');
Route::get('jobs/list', 'JobController@list')->middleware('api');
Route::get('proposals', 'ProposalController@index')->middleware('api');
Route::post('proposals', 'ProposalController@create')->middleware('api');
Route::get('proposals/{id}', 'ProposalController@show')->where('id', '[0-9]+')->middleware('api');
Route::put('proposals/{id}', 'ProposalController@update')->where('id', '[0-9]+')->middleware('api');
Route::get('proposals/{id}/updateStatus', 'ProposalController@updateStatus')->where('id', '[0-9]+')->middleware('api');
Route::resource('assets', 'AssetController')->middleware('api');
Route::get('assetAssigns/logined', 'AssetAssignController@logined')->middleware('api');
Route::resource('assetAssigns', 'AssetAssignController')->middleware('api');
Route::put('assetAssigns/{id}/unassign', 'AssetAssignController@unassign')->where('id', '[0-9]+')->middleware('api');
Route::resource('timeoffs', 'TimeoffController')->middleware('api');
Route::resource('photos', 'PhotoController')->middleware('api');
Route::get('commissionGroups', 'CommissionGroupController@index')->middleware('api');
Route::resource('commissions', 'CommissionController')->middleware('api');
Route::get('invoices/list', 'InvoiceController@list')->middleware('api');
Route::resource('invoices', 'InvoiceController')->middleware('api');
Route::put('invoices/{id}/updateStatus', 'InvoiceController@updateStatus')->where('id', '[0-9]+')->middleware('api');
Route::put('invoices/{id}/paid', 'InvoiceController@paid')->where('id', '[0-9]+')->middleware('api');
Route::resource('invoiceItems', 'InvoiceItemController')->only(['store', 'destroy'])->middleware('api');
Route::get('invoiceItems/worksnap', 'InvoiceItemController@worksnap')->middleware('api');
Route::get('config', 'ConfigController@index')->middleware('api');
Route::post('config', 'ConfigController@save')->middleware('api');
Route::get('config/dashboard', 'ConfigController@dashboard')->middleware('api');
Route::resource('events', 'EventController')->middleware('api');
Route::resource('documents', 'DocumentController')->only(['store', 'destroy'])->middleware('api');
Route::post('documents/{id}', 'DocumentController@update')->where('id', '[0-9]+')->middleware('api');
Route::get('reports/payroll', 'ReportController@payroll')->middleware('api');
Route::get('reports/revenueMonth', 'ReportController@revenueMonth')->middleware('api');
Route::get('reports/companyMembers', 'ReportController@companyMembers')->middleware('api');
Route::get('reports/revenue', 'ReportController@revenue')->middleware('api');