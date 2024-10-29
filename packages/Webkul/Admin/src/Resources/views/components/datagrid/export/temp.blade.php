<table>
    <thead>
        <tr>
            @foreach ($columns as $key => $value)
                <th>{{ $value == 'increment_id' ? 'order_id' : $value }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @foreach ($records as $record)
            <tr>
                @foreach ($record as $column => $value)
                    @if (isset($record->status) &&
                            ($record->status == 'closed' || $record->status == 'canceled') &&
                            $column == 'base_grand_total')
                        <td>-{{ $value }} </td>
                    @else
                        <td>{{ $value }} </td>
                    @endif
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
