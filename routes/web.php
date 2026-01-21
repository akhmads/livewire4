<?php

Route::middleware(['auth'])->group(function () {

    Route::redirect('/', '/home');
    Route::livewire('/home', 'pages::home')->name('home');

    Route::livewire('/users', 'pages::users.index')->name('users.index');
    Route::livewire('/users/create', 'pages::users.create')->name('users.create');
    Route::livewire('/users/{user}/edit', 'pages::users.edit')->name('users.edit');
    Route::livewire('/users/profile', 'pages::users.profile')->name('users.profile');
    Route::livewire('/users/2fa', 'pages::users.2fa')->name('users.2fa');

    Route::livewire('/jobs/queue', 'pages::jobs.queue')->name('jobs.queue');
    Route::livewire('/jobs/failed', 'pages::jobs.failed')->name('jobs.failed');

    Route::livewire('/contact', 'pages::contact.index')->name('contact.index');
    Route::livewire('/contact/create', 'pages::contact.create')->name('contact.create');
    Route::livewire('/contact/{contact}/edit', 'pages::contact.edit')->name('contact.edit');

});

require __DIR__.'/auth.php';
require __DIR__.'/template.php';
