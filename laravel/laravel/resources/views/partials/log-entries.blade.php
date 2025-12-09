<!-- partials/log-entries.blade.php -->
@foreach ($logEntries as $index => $entry)
    @php
        $lineClass = '';
        if (strpos($entry, 'ERROR') !== false) {
            $lineClass = 'error-line';
        } elseif (strpos($entry, 'WARNING') !== false) {
            $lineClass = 'warning-line';
        } elseif (strpos($entry, 'INFO') !== false) {
            $lineClass = 'info-line';
        }

        $entryType = str_replace('-line', '', $lineClass) ?: 'normal';
    @endphp
    <div class="log-line" data-type="{{ $entryType }}">
        <span class="line-number">{{ $index + 1 }}</span>
        <span class="line-content {{ $lineClass }}">{{ $entry }}</span>
    </div>
@endforeach
