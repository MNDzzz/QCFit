<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ImageProcessingController extends Controller
{
    public function removeBackground(Request $request)
    {
        $request->validate([
            'image_url' => 'required|url'
        ]);

        // Mocking the Background Removal Service
        // Simulating delay
        sleep(2);

        // Return a mock result
        return response()->json([
            'original' => $request->image_url,
            'processed_url' => $request->image_url, 
            'message' => 'Background removed successfully (Mock)'
        ]);
    }
}
