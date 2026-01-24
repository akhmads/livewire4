<?php

use App\Models\Contact;
use Livewire\Component;
use Spatie\SimpleExcel\SimpleExcelWriter;

new class extends Component
{
    public function export()
    {
        $contacts = Contact::orderBy('id','asc');
        $writer = SimpleExcelWriter::streamDownload('Contact.xlsx');
        foreach ( $contacts->lazy() as $contact ) {
            $writer->addRow([
                'id' => $contact->id ?? '',
                'name' => $contact->name ?? '',
                'email' => $contact->email ?? '',
                'phone' => $contact->phone ?? '',
                'mobile' => $contact->mobile ?? '',
                'address' => $contact->address ?? '',
            ]);
        }
        return response()->streamDownload(function() use ($writer){
            $writer->close();
        }, 'Contact.xlsx');
    }
};
?>

@placeholder
<div>
    <x-button label="Export" icon="o-document-arrow-down" />
</div>
@endplaceholder

<div>
    {{-- EXPORT BUTTON --}}
    <x-button
        label="Export"
        wire:click="export"
        spinner="export"
        icon="o-document-arrow-down"
        responsive />
</div>
