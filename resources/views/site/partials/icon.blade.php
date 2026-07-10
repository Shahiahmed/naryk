@php
    use App\Support\Icons;
    $path = Icons::path($name);
@endphp

@if ($path)
    <svg class="icon {{ $class ?? '' }}" viewBox="0 0 24 24" fill="currentColor"
         width="20" height="20" aria-hidden="true" focusable="false">
        <path d="{{ $path }}"></path>
    </svg>
@endif
