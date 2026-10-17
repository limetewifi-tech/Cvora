<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Services\Ai\AiCareerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumeCoachController extends Controller
{
    public function store(Request $request, Resume $resume, AiCareerService $ai): JsonResponse
    {
        $this->authorize('update', $resume);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['nullable', 'array', 'max:8'],
            'history.*.role' => ['required', 'in:user,assistant'],
            'history.*.content' => ['required', 'string', 'max:2000'],
        ]);

        $history = collect($data['history'] ?? [])
            ->take(-6)
            ->map(fn (array $item) => [
                'role' => $item['role'],
                'content' => trim($item['content']),
            ])
            ->filter(fn (array $item) => $item['content'] !== '')
            ->values()
            ->all();

        $proposal = $ai->coach($request->user(), [
            'message' => trim($data['message']),
            'history' => $history,
            'resume' => $this->snapshot($resume),
        ]);

        return response()->json([
            'successful' => $proposal->successful,
            'reply' => mb_substr(trim((string) ($proposal->data['reply'] ?? '')), 0, 4000),
            'suggestions' => $proposal->successful ? $this->suggestions($proposal->data) : [],
            'missing' => $proposal->missing,
            'warnings' => $proposal->warnings,
            'error' => $proposal->error,
            'credits_remaining' => $proposal->creditsRemaining,
        ], $proposal->successful ? 200 : 422);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Resume $resume): array
    {
        $personal = is_array($resume->personal) ? $resume->personal : [];
        $content = is_array($resume->content) ? $resume->content : [];

        return [
            'headline' => (string) ($personal['headline'] ?? ''),
            'summary' => (string) $resume->summary,
            'experiences' => array_values($content['experiences'] ?? []),
            'education' => array_values($content['education'] ?? []),
            'skills' => array_values($content['skills'] ?? []),
            'languages' => array_values($content['languages'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array{target: string, index: int|null, text: string, label: string}>
     */
    private function suggestions(array $data): array
    {
        $allowed = ['summary', 'headline', 'experience', 'education', 'skill'];
        $suggestions = [];

        foreach ($data['suggestions'] ?? [] as $item) {
            if (! is_array($item) || ! in_array($item['target'] ?? '', $allowed, true)) {
                continue;
            }

            $text = trim((string) ($item['text'] ?? ''));

            if ($text === '') {
                continue;
            }

            $index = $item['index'] ?? null;
            $label = trim((string) ($item['label'] ?? 'Proposition'));

            $suggestions[] = [
                'target' => (string) $item['target'],
                'index' => is_numeric($index) ? max(0, (int) $index) : null,
                'text' => mb_substr($text, 0, 4000),
                'label' => mb_substr($label !== '' ? $label : 'Proposition', 0, 120),
            ];
        }

        return $suggestions;
    }
}
