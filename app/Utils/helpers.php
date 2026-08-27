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
