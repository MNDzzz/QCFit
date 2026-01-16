<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function removeBackground(Request $request)
    {
        $request->validate([
            'image_url' => 'required|url'
        ]);

        // Mocking the AI Service
        // In real life: HTTP POST to Python Microservice -> receive Base64/URL back

        // Simulating delay
        sleep(2);

        // Return a mock result (For demo purposes, we return the same URL but with a flag or just the same URL)
        // Ideally we would return a processed image URL.
        return response()->json([
            'original' => $request->image_url,
            'processed_url' => $request->image_url, // In a real demo I'd replace this with a transparency mock if I had one
            'message' => 'Background removed successfully (Mock)'
        ]);
    }
}
