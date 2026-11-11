<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class QuizController extends Controller
{
    /**
     * Load the question bank from resources/data/questions.json.
     * Cached for an hour so the file isn't re-parsed on every request.
     */
    private function bank(): array
    {
        return Cache::remember('mla_c01_questions', 3600, function () {
            $path = resource_path('data/questions.json');
            $data = json_decode(file_get_contents($path), true);

            return is_array($data) ? $data : [];
        });
    }

    /** Topic list (slug, name, question count) in first-seen order. */
    private function topics(array $bank): array
    {
        $topics = [];
        foreach ($bank as $q) {
            $slug = $q['topic_slug'];
            $topics[$slug] ??= ['slug' => $slug, 'name' => $q['topic'], 'count' => 0];
            $topics[$slug]['count']++;
        }

        return array_values($topics);
    }

    public function home(): View
    {
        $bank = $this->bank();

        return view('home', [
            'total' => count($bank),
            'topics' => $this->topics($bank),
        ]);
    }

    public function quiz(): View
    {
        $bank = $this->bank();

        return view('quiz', [
            'total' => count($bank),
            'topics' => $this->topics($bank),
        ]);
    }

    public function about(): View
    {
        return view('about', ['total' => count($this->bank())]);
    }

    public function questions(): JsonResponse
    {
        return response()
            ->json($this->bank())
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
