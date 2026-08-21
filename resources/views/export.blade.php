<table>
    <thead>
    <tr>
        @foreach($headers as $header)
        <th>{{ $header }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
    <tr>
        @foreach($item as $key => $value)
            <td>{{ $value }}</td>
        @endforeach
    </tr>
    @endforeach
    </tbody>
</table>