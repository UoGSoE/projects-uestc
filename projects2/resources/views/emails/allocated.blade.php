@component('mail::message')
# Student Projects

You have been accepted onto the project '{{ $project->title }}'.  Please get in touch
with the project supervisor at {{ $project->owner->email }}.

Thanks,<br>
{{ config('app.name') }}

@endcomponent
