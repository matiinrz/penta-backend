<?php

namespace App\Http\Controllers;

use App\Models\ChatLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
/**
 * @OA\Info(title="Penta Chatbot API", version="1.0")
 */
class ChatController extends Controller
{
    /**
     * Handle incoming chat messages and get responses from the AI.
     *
     * @OA\Post(
     *     path="/api/chat",
     *     summary="Handle AI chat",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="messages", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chat response",
     *         @OA\JsonContent(
     *             @OA\Property(property="response", type="string")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Bad Request")
     * )
     */
    public function handleChat(Request $request)
    {
        $validated = $request->validate([
            'messages' => 'required|array',
        ]);

        $apiUrl = 'https://api.talkbot.ir/v1/chat/completions';
        $apiKey = 'sk-e19d67877798d11ee00df66cd10b24ea'; // Replace with your actual API key
        $payload = [
            "model" => "gpt-4o-mini",
            "messages" => $validated['messages'],
            "max-token" => 4000,
            "temperature" => 0.3,
            "stream" => false,
            "top_p" => 1.0,
            "frequency_penalty" => 0.0,
            "presence_penalty" => 0.0,
        ];

        $response = $this->callChatbotAPI($apiUrl, $apiKey, $payload);

        // Log chat details
        ChatLog::create([
            'user_id' => Auth::id(),
            'input_messages' => json_encode($validated['messages']),
            'response' => $response,
        ]);

        return response()->json(['response' => json_decode($response)], 200);
    }

    private function callChatbotAPI($url, $key, $payload)
    {
        $headers = [
            'Content-Type: application/json',
            "Authorization: $key",
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch));
        }

        curl_close($ch);

        return $response;
    }
}
