@props(['title', 'message'])

<div {{ $attributes->merge(['class' => 'card border-0 bg-light']) }}><div class="card-body text-center py-5"><h2 class="h5">{{ $title }}</h2><p class="text-muted mb-0">{{ $message }}</p></div></div>
