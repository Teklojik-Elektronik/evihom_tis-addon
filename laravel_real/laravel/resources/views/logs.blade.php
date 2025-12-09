<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Device Manager Logs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    {{-- <link rel="stylesheet" href="{{ asset('css/log-viewer.css') }}"> --}}
    @vite(['resources/css/log-viewer.css', 'resources/js/log-viewer.js'])

    <script src="https://cdn.jsdelivr.net/npm/sweetalert@2.1.2/dist/sweetalert.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @include('partials.header')

    <div class="log-container">
        @include('partials.search-bar')
        <div class="log-box">
            @include('partials.log-toolbar')
            <div class="log-body">
                <div class="log-table">
                    @include('partials.log-entries', ['logEntries' => $logEntries])
                </div>
            </div>
        </div>
    </div>

    {{-- <script src="{{ asset('js/log-viewer.js') }}"></script> --}}
</body>
</html>
