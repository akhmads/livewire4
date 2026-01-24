<?php

use Mary\Traits\Toast;
use Livewire\Component;
use App\Models\QueueLog;
use App\Jobs\ImportContacts;
use Livewire\WithFileUploads;

new class extends Component
{
    use Toast, WithFileUploads;

    public $importFile;
    public bool $importModal = false;

    public function import(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        $target = $this->importFile->path();

        // Create QueueLog for monitoring
        $queueLog = QueueLog::create([
            'job_name' => 'App\Jobs\ImportContacts',
            'status' => 'pending',
            'data' => [
                'file' => $target,
                'user_id' => auth()->id(),
            ],
        ]);

        ImportContacts::dispatch($queueLog);

        $this->success(
            'Import Started',
            'Your import has begun will be processed in the background.<br>You will receive a notification when it is complete.',
            timeout: 10000
        );

        $this->importModal = false;
        $this->importFile = null;
    }
};
?>

@placeholder
<div>
    <x-button label="Import" responsive icon="o-document-arrow-up" class="opacity-40" />
</div>
@endplaceholder

<div
    x-data="{ uploading: false }"
    x-on:livewire-upload-start="uploading = true"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-cancel="uploading = false"
    x-on:livewire-upload-error="uploading = false"
>
    {{-- IMPORT BUTTON --}}
    <x-button label="Import" @click="$wire.importModal = true" responsive icon="o-document-arrow-up" />

    {{-- IMPORT MODAL --}}
    <x-modal wire:model="importModal">
        <x-form wire:submit="import">
            <div class="p-4">
                <h2 class="text-lg font-semibold mb-4">Import Contacts</h2>

                <p class="text-sm text-gray-600 mb-4">
                    Upload an Excel or CSV file with columns: name, email, phone, mobile, address.<br />
                    <a href="{{ route('contacts.template.download') }}" class="text-blue-500 underline">Download Template</a>
                </p>

                <x-file
                    wire:model="importFile"
                    label="Select File"
                    accept=".xlsx,.xls,.csv"
                    wire:loading.attr="disabled"
                    x-bind:disabled="uploading"
                />
            </div>
            <x-slot:actions>
                <x-button
                    label="Cancel"
                    @click="$wire.importModal = false"
                    class="mr-2"
                    x-bind:disabled="uploading"
                    wire:loading.attr="disabled"
                />
                <x-button
                    label="Import"
                    type="submit"
                    spinner="import"
                    class="btn-primary"
                    x-bind:disabled="uploading"
                    wire:loading.attr="disabled"
                />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
