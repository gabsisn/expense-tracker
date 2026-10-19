<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expense Tracker</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <div id="app" data-expenses='@json($expenses)'>
        {{-- Fallback for curl / no-JS: same rows, plain HTML --}}
        <table>
            @foreach ($expenses as $e)
                <tr><td>{{ $e->spent_on->toDateString() }}</td><td>{{ $e->description }}</td><td>{{ $e->amount }}</td></tr>
            @endforeach
        </table>
    </div>
</body>
</html>
