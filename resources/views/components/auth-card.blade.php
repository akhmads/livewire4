<div {{ $attributes->merge(['class' => 'px-2 lg:px-0 flex items-center justify-center min-h-dvh bg-gray-100 bg-cover bg-no-repeat bg-center dark:bg-none dark:bg-neutral-900']) }}>
    <div class="w-md shadow">
        <div class="bg-white dark:bg-neutral-800 p-8 space-y-6">
            {{ $slot }}
        </div>
    </div>
</div>
