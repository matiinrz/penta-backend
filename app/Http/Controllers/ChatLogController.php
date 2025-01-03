<?php

namespace App\Http\Controllers;

use App\Models\ChatLog;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="ChatLog",
 *     type="object",
 *     title="Chat Log",
 *     description="Chat log resource",
 *     @OA\Property(property="id", type="integer", description="Chat log ID"),
 *     @OA\Property(property="user_id", type="integer", description="ID of the user who sent the chat"),
 *     @OA\Property(property="message", type="string", description="Chat message"),
 *     @OA\Property(property="response", type="string", description="AI response"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp of when the chat was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp of the last update"),
 * )
 */


class ChatLogController extends Controller
{
    /**
     * Get the chat logs (Admin only).
     *
     * @OA\Get(
     *     path="/api/chat-logs",
     *     summary="Get chat logs",
     *     tags={"Chat Logs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of chat logs",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ChatLog")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */

    public function index(Request $request)
    {
        $this->authorize('viewAny', ChatLog::class); // Ensure permission checks

        $logs = ChatLog::with('user')->paginate(10);
        return response()->json($logs);
    }

    /**
     * Delete a chat log (Admin only).
     *
     * @OA\Delete(
     *     path="/api/chat-logs/{id}",
     *     summary="Delete chat log",
     *     tags={"Chat Logs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the chat log to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Chat log deleted"),
     *     @OA\Response(response=404, description="Chat log not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function destroy($id)
    {
        $this->authorize('delete', ChatLog::class); // Ensure permission checks

        $log = ChatLog::findOrFail($id);
        $log->delete();

        return response()->json(['message' => 'Log deleted successfully']);
    }
}
