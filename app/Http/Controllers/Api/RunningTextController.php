<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AlumniDirectory;
use Illuminate\Http\Request;

class RunningTextController extends Controller
{
    /**
     * Get Alumni Messages formatted for LED Running Text (HD-WF2 / ESP32)
     */
    public function getAlumniMessages(Request $request)
    {
        $limit = min((int) $request->input('limit', 20), 50);
        $schoolId = $request->input('school_id');
        $format = strtolower($request->input('format', 'json'));
        $separator = $request->input('separator', '   ***   ');

        $query = AlumniDirectory::with('school')
            ->where('is_approved', true)
            ->whereNotNull('message')
            ->where('message', '!=', '')
            ->latest();

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $alumnis = $query->take($limit)->get();

        $formattedMessages = [];
        foreach ($alumnis as $alumni) {
            $schoolName = $alumni->school ? $alumni->school->name : 'PEMBDA';
            // Clean up newlines in message for single-line LED scrolling
            $cleanMsg = trim(preg_replace('/\s+/', ' ', $alumni->message));
            
            $formatted = sprintf(
                "[%s (%s - %s) : \"%s\"]",
                $alumni->full_name,
                $schoolName,
                $alumni->graduation_year,
                $cleanMsg
            );

            $formattedMessages[] = [
                'id' => $alumni->id,
                'name' => $alumni->full_name,
                'school' => $schoolName,
                'graduation_year' => $alumni->graduation_year,
                'message' => $cleanMsg,
                'formatted' => $formatted,
            ];
        }

        $runningTextString = implode($separator, array_column($formattedMessages, 'formatted'));
        
        if (empty($runningTextString)) {
            $runningTextString = "*** SELAMAT DATANG DI PERGURUAN PEMBDA ***";
        }

        // Return plain text if requested (useful for minimal microcontrollers)
        if ($format === 'raw' || $format === 'text' || $request->is('*/raw')) {
            return response($runningTextString, 200)
                ->header('Content-Type', 'text/plain; charset=utf-8');
        }

        return response()->json([
            'status' => 'success',
            'total' => count($formattedMessages),
            'separator' => $separator,
            'running_text' => $runningTextString,
            'data' => $formattedMessages,
        ]);
    }
}
