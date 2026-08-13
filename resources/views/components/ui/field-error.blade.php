@props(['field'])

@error($field)
    <div class="invalid-feedback">{{ $message }}</div>
@enderror
