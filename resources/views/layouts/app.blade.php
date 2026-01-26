<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800" rel="stylesheet" />

    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-inter antialiased bg-base-200">

    {{-- The navbar with `sticky` and `full-width` --}}
    <x-nav id="nav" sticky full-width class="h-[65px] z-20 ">
        <x-slot:brand>
            {{-- Drawer toggle for "main-drawer" --}}
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>

            {{-- Brand --}}
            <x-app-brand />
        </x-slot:brand>

        {{-- Right side actions --}}
        <x-slot:actions>
            <div class="flex items-center gap-0.5 py-1">
                <x-theme-toggle class="btn btn-ghost btn-sm" />

                @if (view()->exists('components.notification-bell'))
                    <livewire:notification-bell />
                @endif

                @if($user = auth()->user())
                <x-dropdown>
                    <x-slot:trigger>
                        <x-button class="btn btn-ghost btn-sm" responsive>
                            <x-avatar :title="\Illuminate\Support\Str::limit($user->name, 20)" image="{{ $user->avatar ?? asset('assets/img/default-avatar.png') }}" class="h-6" />
                        </x-button>
                    </x-slot:trigger>
                    <x-menu-item title="My Profile" link="{{ route('users.profile') }}" icon="o-user" />
                    <x-menu-separator />
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-button label="Log Out" type="submit" icon="o-power" class="w-full btn-sm btn-error btn-soft min-w-36" />
                    </form>
                </x-dropdown>
                @endif
            </div>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main with-nav full-width>

        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" class="bg-base-100 lg:bg-white lg:border-r lg:border-gray-200 dark:lg:bg-inherit dark:lg:border-none">

            {{-- MENU --}}
            <x-menu activate-by-route class="text-[13px] font-light">
                <x-menu-item title="Home" icon="o-home" link="{{ route('home') }}" />
                <x-menu-item title="Contact" icon="o-identification" link="{{ route('contact.index') }}" :hidden="auth()->user()->cannot('contacts.view')" />
                <x-menu-item title="Users" icon="o-users" link="{{ route('users.index') }}" :hidden="auth()->user()->cannot('users.view')" />

                <x-menu-sub title="Queue Monitor" icon="o-bolt">
                    <x-menu-item title="Job History" icon="o-queue-list" link="{{ route('queue.jobs') }}" :hidden="auth()->user()->cannot('queue.view')" />
                    <x-menu-item title="Failed Jobs" icon="o-x-circle" link="{{ route('queue.failed') }}" :hidden="auth()->user()->cannot('queue.view')" />
                </x-menu-sub>

                <x-menu-sub title="Access Control" icon="o-shield-check">
                    <x-menu-item title="Permissions" icon="o-key" link="{{ route('permissions.index') }}" :hidden="auth()->user()->cannot('permissions.view')" />
                    <x-menu-item title="Roles" icon="o-user-group" link="{{ route('roles.index') }}" :hidden="auth()->user()->cannot('roles.view')" />
                    <x-menu-item title="User Roles" icon="o-identification" link="{{ route('user-roles.index') }}" :hidden="auth()->user()->cannot('user-roles.view')" />
                </x-menu-sub>
            </x-menu>
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />

    {{-- Theme toggle --}}
    <x-theme-toggle class="hidden" />

    @livewireScripts
</body>
</html>
