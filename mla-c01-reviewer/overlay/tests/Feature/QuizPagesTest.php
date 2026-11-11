<?php

namespace Tests\Feature;

use Tests\TestCase;

class QuizPagesTest extends TestCase
{
    public function test_public_pages_load(): void
    {
        foreach (['/', '/quiz', '/about'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_home_mentions_the_exam(): void
    {
        $this->get('/')->assertSee('MLA-C01');
    }

    public function test_study_guide_page_loads(): void
    {
        $this->get('/guide')->assertOk()->assertSee('Bedrock Guardrails')->assertSee('AgentCore');
    }

    public function test_question_bank_is_valid(): void
    {
        $response = $this->getJson('/question-bank')->assertOk();
        $bank = $response->json();

        $this->assertNotEmpty($bank);

        foreach ($bank as $q) {
            $this->assertCount(4, $q['options'], "Question {$q['id']} must have 4 options");
            $this->assertGreaterThanOrEqual(0, $q['correct']);
            $this->assertLessThanOrEqual(3, $q['correct']);
            $this->assertNotEmpty($q['takeaway']);
            foreach ($q['options'] as $opt) {
                $this->assertNotEmpty($opt['text']);
                $this->assertNotEmpty($opt['why']);
            }
        }
    }
}
