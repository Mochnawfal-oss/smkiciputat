<?php

namespace Tests\Feature;

use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_all_published_teachers_beyond_six_records(): void
    {
        $teacherNames = [];

        for ($index = 1; $index <= 10; $index++) {
            $teacherName = "Tenaga Pendidik {$index}";
            $teacherNames[] = $teacherName;

            Teacher::query()->create([
                'name' => $teacherName,
                'slug' => "tenaga-pendidik-{$index}",
                'position' => "Jabatan {$index}",
                'status' => 'published',
                'sort_order' => $index,
            ]);
        }

        Teacher::query()->create([
            'name' => 'Tenaga Nonaktif',
            'slug' => 'tenaga-nonaktif',
            'position' => 'Jabatan nonaktif',
            'status' => 'draft',
            'sort_order' => 11,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();

        foreach ($teacherNames as $teacherName) {
            $response->assertSee($teacherName);
        }

        $response->assertDontSee('Tenaga Nonaktif');
    }
}
