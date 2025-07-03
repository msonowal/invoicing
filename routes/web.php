<?php

use App\Livewire\CompanyProfile;
use App\Livewire\CustomerForm;
use App\Livewire\CustomerList;
use App\Livewire\InvoiceForm;
use App\Livewire\InvoiceList;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    Route::get('/customers', CustomerList::class)->name('customers.index');
    Route::get('/customers/create', CustomerForm::class)->name('customers.create');
    Route::get('/customers/{customer}/edit', CustomerForm::class)->name('customers.edit');

    Route::get('/invoices', InvoiceList::class)->name('invoices.index');
    Route::get('/invoices/create', InvoiceForm::class)->name('invoices.create');
    Route::get('/invoices/{invoice}/edit', InvoiceForm::class)->name('invoices.edit');
});
