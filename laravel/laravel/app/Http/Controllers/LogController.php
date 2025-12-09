<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends Controller
{
    /**
     * Show the log viewer page
     */
    public function index()
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = File::exists($logFile) ? File::get($logFile) : 'Log file not found.';

        // Parse logs to preserve multi-line entries
        $logEntries = $this->parseLogEntries($logs);

        return view('logs', ['logEntries' => $logEntries]);
    }

    /**
     * Return logs via AJAX
     */
    public function ajaxRefresh()
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = File::exists($logFile) ? File::get($logFile) : 'Log file not found.';

        // Parse logs to preserve multi-line entries
        $logEntries = $this->parseLogEntries($logs);

        // Render just the log entries HTML
        $html = view('partials.log-entries', ['logEntries' => $logEntries])->render();

        return response()->json([
            'html' => $html,
            'count' => count($logEntries)
        ]);
    }

    /**
     * Download the log file
     */
    public function download()
    {
        $logFile = storage_path('logs/laravel.log');

        if (File::exists($logFile)) {
            return response()->download($logFile);
        }

        return redirect()->back()->with('error', 'Log file not found.');
    }

    /**
     * Parse log file content into separate entries preserving multi-line format
     */
    private function parseLogEntries($logContent)
    {
        // Pattern to identify the beginning of a log entry
        $pattern = '/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]/m';
        $logEntries = [];
        $currentEntry = '';

        foreach (explode("\n", $logContent) as $line) {
            // If this line starts a new log entry
            if (preg_match($pattern, $line)) {
                if (!empty($currentEntry)) {
                    $logEntries[] = $currentEntry;
                }
                $currentEntry = $line;
            } else {
                // This is a continuation of the current entry
                if (!empty($currentEntry)) {
                    $currentEntry .= "\n" . $line;
                } else {
                    // This might happen if the log file starts with non-standard entries
                    $currentEntry = $line;
                }
            }
        }

        // Add the last entry if it exists
        if (!empty($currentEntry)) {
            $logEntries[] = $currentEntry;
        }

        return $logEntries;
    }

    public function clear()
    {
        $logFile = storage_path('logs/laravel.log');

        if (File::exists($logFile)) {
            File::put($logFile, '');
            return redirect()->back()->with('success', 'Log file cleared successfully.');
        }

        return redirect()->back()->with('error', 'Log file not found.');
    }
}
