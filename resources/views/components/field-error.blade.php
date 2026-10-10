@props(['field'])
@error($field)
    <p id="{{ $field }}-error" class="mt-1 text-sm text-red-700" role="alert">{{ $message }}</p>
@enderror
