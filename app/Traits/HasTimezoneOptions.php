<?php

namespace App\Traits;

trait HasTimezoneOptions
{
    public function timezoneOptions(string $placeholder = '-- Select Timezone --'): array
    {
        $timezones = \DateTimeZone::listIdentifiers();
        $array = [];
        $array[0]['id'] = '';
        $array[0]['name'] = $placeholder;

        foreach ($timezones as $index => $timezone) {
            $array[$index + 1]['id'] = $timezone;
            $array[$index + 1]['name'] = $timezone;
        }

        return $array;
    }

    public function timezoneFilterOptions(): array
    {
        return $this->timezoneOptions('-- All --');
    }
}
