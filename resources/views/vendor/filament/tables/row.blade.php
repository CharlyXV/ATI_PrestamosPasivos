
@props([
    'columns',
    'record',
    'rowLoop',
])

<tr
    @if ($record->plp_estados === 'completado')
        style="opacity: 0.7; pointer-events: none; background-color: #f9fafb;"
        class="completed-row"
    @else
        class="active-row"
    @endif
    wire:key="row-{{ $record->getKey() }}"
>
    @foreach ($columns as $column)
        @php
            $isHidden = $column->isHidden();
            $columnClasses = \Illuminate\Support\Arr::toCssClasses([
                'hidden' => $isHidden,
                'completed-cell' => $record->plp_estados === 'completado',
            ]);
        @endphp

        <td class="{{ $columnClasses }}">
            {{ $column->render($record) }}
        </td>
    @endforeach
</tr>