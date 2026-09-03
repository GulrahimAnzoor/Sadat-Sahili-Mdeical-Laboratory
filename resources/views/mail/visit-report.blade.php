<x-mail::message>
# {{ __('Lab report — :name', ['name' => $visit->patient->name]) }}

{{ __('Sadat Salihi Medical Laboratory') }}

- {{ __('File no.') }}: {{ $visit->patient->file_number }}
- {{ __('Queue no.') }}: {{ $visit->queue_number }}
- {{ __('Ref By') }}: {{ $visit->referrerLabel() }}

{{ __('Open the report from the laboratory:') }}

<x-mail::button :url="route('visits.report', $visit)">
{{ __('View report') }}
</x-mail::button>

{{ implode(' · ', config('lab.phones')) }}
</x-mail::message>
