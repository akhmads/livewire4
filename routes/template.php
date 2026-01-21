<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('/contacts/template', function () {
        $rows = [];
        for ($i = 1; $i <= 20; $i++) {
            $rows[] = [
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'mobile' => fake()->phoneNumber(),
                'address' => fake()->address(),
            ];
        }

        return Spatie\SimpleExcel\SimpleExcelWriter::streamDownload('contacts_template.csv')
            ->addRows($rows)
            ->toBrowser();
    })->name('contacts.template.download');

});
