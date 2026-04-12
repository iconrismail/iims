<?php

namespace App\Http\Controllers;

use App\Services\HrChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function chat(Request $request, HrChatbotService $chatbot): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array|max:20',
            'history.*.role'    => 'required|in:user,assistant',
            'history.*.content' => 'required|string|max:2000',
        ]);

        $reply = $chatbot->chat(
            $request->user(),
            $request->input('message'),
            $request->input('history', [])
        );

        return response()->json(['reply' => $reply]);
    }
}
