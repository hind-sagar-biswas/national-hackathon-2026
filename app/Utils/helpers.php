<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('handleErrorResponse')) {
    /**
     * Returns a customized greeting.
     */
    function handleErrorResponse(Exception $e, ?array $data, string $message = 'Oops! Something went wrong. Please try again later.', bool $json = false)
    {
        if (app()->hasDebugModeEnabled()) {
            throw $e;
        }

        // Log the exception with the provided data
        logger()->error($e->getMessage(), [
            'user' => Auth::check() ? Auth::user()->id.': '.Auth::user()->name.' ('.Auth::user()->email.')' : 'Guest User',
            'data' => $data,
            'trace' => $e->getTraceAsString(),
        ]);

        if ($json) {
            return response()->json(['message' => $message], 500);
        }

        return back()->dangerBanner($message);
    }
}

if (! function_exists('toPaisa')) {
    /**
     * Convert Tk (BDT) to integer Paisa (1 Tk = 100 Paisa).
     */
    function toPaisa(float|int|string $taka): int
    {
        return (int) round(((float) $taka) * 100);
    }
}

if (! function_exists('fromPaisa')) {
    /**
     * Convert integer Paisa to Tk (BDT).
     */
    function fromPaisa(?int $paisa): float
    {
        return ((float) ($paisa ?? 0)) / 100;
    }
}

if (! function_exists('formatPaisa')) {
    /**
     * Format integer Paisa as a decimal BDT string (e.g. 50000 -> 500.00).
     */
    function formatPaisa(?int $paisa): string
    {
        return number_format(((float) ($paisa ?? 0)) / 100, 2);
    }
}
