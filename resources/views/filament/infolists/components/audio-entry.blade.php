<x-dynamic-component
    :component="$getEntryWrapperView()"
    :entry="$entry"
>
    <div {{ $getExtraAttributeBag() }}>
        {{$getSound($getState())}}
    </div>
</x-dynamic-component>
