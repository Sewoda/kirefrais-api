<?php
// app/Http/Controllers/AiAssistantController.php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AiService;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function __construct(private AiService $ai) {}

    // POST /api/ai/chat
    public function chat(Request $request)
    {
        $request->validate([
            'message'         => 'required|string|min:2|max:1000',
            'conversation_id' => 'nullable|exists:ai_conversations,id',
        ]);

        $user = $request->user();

        // Si l'utilisateur est connecté, on gère l'historique en base
        if ($user) {
            $conversation = $request->conversation_id
                ? AiConversation::where('user_id', $user->id)->findOrFail($request->conversation_id)
                : AiConversation::create([
                    'user_id' => $user->id,
                    'title'   => $this->makeTitle($request->message),
                ]);

            $history = $conversation->messages()
                ->latest()->take(10)->get()->reverse()
                ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
                ->toArray();

            $conversation->messages()->create([
                'role'    => 'user',
                'content' => $request->message,
            ]);
        } else {
            // Invité : pas d'historique persistant
            $history      = [];
            $conversation = null;
        }

        $response = $this->ai->chat($request->message, $history);

        if ($user && $conversation) {
            $conversation->messages()->create([
                'role'     => 'assistant',
                'content'  => $response['reply'],
                'metadata' => [
                    'type'    => $response['type'],
                    'steps'   => $response['steps'],
                    'tips'    => $response['tips'],
                    'kit_ids' => array_column($response['kits'], 'id'),
                ],
            ]);

            $conversation->increment('message_count');
        }

        return response()->json([
            'conversation_id' => $conversation?->id,
            'reply'           => $response['reply'],
            'type'            => $response['type'],
            'steps'           => $response['steps'],
            'tips'            => $response['tips'],
            'kits'            => $response['kits'],
        ]);
    }

    // GET /api/ai/conversations
    public function conversations(Request $request)
    {
        return response()->json(
            AiConversation::where('user_id', $request->user()->id)
                ->latest()->take(20)
                ->get(['id', 'title', 'message_count', 'created_at'])
        );
    }

    // GET /api/ai/conversations/{id}
    public function conversation(Request $request, int $id)
    {
        return response()->json(
            AiConversation::where('user_id', $request->user()->id)
                ->with('messages')->findOrFail($id)
        );
    }

    // DELETE /api/ai/conversations/{id}
    public function deleteConversation(Request $request, int $id)
    {
        AiConversation::where('user_id', $request->user()->id)->findOrFail($id)->delete();
        return response()->json(['message' => 'Conversation supprimée.']);
    }

    private function makeTitle(string $message): string
    {
        return strlen($message) > 50 ? substr($message, 0, 47) . '...' : $message;
    }
}
