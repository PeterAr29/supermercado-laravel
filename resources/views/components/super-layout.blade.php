@props(['title' => 'Supermercado'])

<x-layouts.super :title="$title">
    {{ $slot }}
</x-layouts.super>
