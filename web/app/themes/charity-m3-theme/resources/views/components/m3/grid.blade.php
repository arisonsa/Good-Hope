@props([
    'cols' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3', // Default responsive columns
    'gap' => 'gap-6',  // Default gap
    'tag' => 'div'
])

<{{ $tag }} {{ $attributes->merge(['class' => "grid {$cols} {$gap}"]) }}>
    {{ $slot }}
</{{ $tag }}>

{{--
Example Usage:

<x-m3.grid cols="1 md:grid-cols-3" gap="8">
    <x-m3.card title="Item 1">Content for item 1.</x-m3.card>
    <x-m3.card title="Item 2">Content for item 2.</x-m3.card>
    <x-m3.card title="Item 3">Content for item 3.</x-m3.card>
    <x-m3.card title="Item 4">Content for item 4.</x-m3.card>
</x-m3.grid>

<x-m3.grid tag="ul" cols="1 sm:grid-cols-2" gap="4" class="list-none p-0">
    @foreach ($items as $item)
        <li class="m-0 p-0">
            <x-m3.card title="{{ $item->title }}">{{ $item->excerpt }}</x-m3.card>
        </li>
    @endforeach
</x-m3.grid>
--}}
