<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\ContentEntry;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CmsContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('site-content.defaults', []) as $key => $value) {
            SiteSetting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'group' => Str::before($key, '.'),
                    'value' => json_encode($value, JSON_THROW_ON_ERROR),
                ],
            );
        }

        foreach ($this->programs() as $program) {
            ContentEntry::query()->firstOrCreate(
                ['type' => 'program', 'route_key' => $program['route_key']],
                [
                    'title' => $program['title'],
                    'slug' => $program['route_key'],
                    'summary' => $program['summary'],
                    'image_path' => $program['image_path'],
                    'status' => 'published',
                    'sort_order' => $program['sort_order'],
                    'metadata' => [
                        'abbreviation' => $program['abbreviation'],
                        'icon' => 'graduation-cap',
                    ],
                ],
            );
        }

        foreach ($this->news() as $article) {
            ContentEntry::query()->firstOrCreate(
                ['type' => 'news', 'slug' => $article['slug']],
                [
                    'title' => $article['title'],
                    'summary' => $article['summary'],
                    'body' => null,
                    'category' => 'Kegiatan Sekolah',
                    'image_path' => $article['image_path'],
                    'status' => 'published',
                    'featured' => $article['group'] === 'featured',
                    'published_at' => $article['date'].' 09:00:00',
                    'metadata' => ['homepage_group' => $article['group']],
                ],
            );
        }

        Admin::query()->firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator',
                'role' => 'superadmin',
                'password' => 'superadmin1234',
            ],
        );
    }

    /**
     * @return list<array{title: string, route_key: string, abbreviation: string, summary: string, image_path: ?string, sort_order: int}>
     */
    private function programs(): array
    {
        return [
            ['title' => 'Kuliner', 'route_key' => 'kuliner', 'abbreviation' => 'KULINER', 'summary' => 'Belajar pengolahan makanan, penyajian, dan keterampilan kuliner.', 'image_path' => '/images/2.jpg', 'sort_order' => 1],
            ['title' => 'DKV', 'route_key' => 'dkv', 'abbreviation' => 'DKV', 'summary' => 'Mengasah kreativitas dalam desain grafis dan komunikasi visual.', 'image_path' => '/images/3.jpg', 'sort_order' => 2],
            ['title' => 'TKJ', 'route_key' => 'tkj', 'abbreviation' => 'TKJ', 'summary' => 'Memahami komputer, jaringan, dan teknologi informasi modern.', 'image_path' => '/images/4.jpg', 'sort_order' => 3],
            ['title' => 'Akuntansi', 'route_key' => 'ak', 'abbreviation' => 'AK', 'summary' => 'Belajar pencatatan transaksi dan pengelolaan keuangan.', 'image_path' => '/images/5.jpg', 'sort_order' => 4],
            ['title' => 'Manajemen Perkantoran', 'route_key' => 'mp', 'abbreviation' => 'MP', 'summary' => 'Mengembangkan kemampuan administrasi dan manajemen kantor.', 'image_path' => null, 'sort_order' => 5],
            ['title' => 'Bisnis Digital', 'route_key' => 'bd', 'abbreviation' => 'BD', 'summary' => 'Mengembangkan kemampuan bisnis dan pemanfaatan teknologi digital.', 'image_path' => '/images/1.jpg', 'sort_order' => 6],
        ];
    }

    /**
     * @return list<array{title: string, slug: string, summary: string, image_path: ?string, date: string, group: string}>
     */
    private function news(): array
    {
        return [
            ['title' => 'Gelar kreativitas siswa menampilkan karya terbaik', 'slug' => 'gelar-kreativitas-siswa', 'summary' => 'Siswa berbagi karya dan pengalaman dalam kegiatan kreatif di lingkungan sekolah.', 'image_path' => 'https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&fit=crop&w=600&q=80', 'date' => '2026-10-28', 'group' => 'featured'],
            ['title' => 'Siswa mengikuti kegiatan belajar dan diskusi bersama', 'slug' => 'belajar-dan-diskusi-bersama', 'summary' => 'Kegiatan kelas mendorong siswa untuk aktif bertukar gagasan dan bekerja sama.', 'image_path' => 'https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&w=360&q=80', 'date' => '2026-10-18', 'group' => 'carousel'],
            ['title' => 'Kegiatan bersama membangun semangat dan kerja tim', 'slug' => 'kegiatan-dan-kerja-tim', 'summary' => 'Siswa berpartisipasi dalam aktivitas yang mengasah kekompakan dan kepedulian.', 'image_path' => 'https://images.unsplash.com/photo-1529390079861-591de354faf5?auto=format&fit=crop&w=360&q=80', 'date' => '2026-10-10', 'group' => 'carousel'],
            ['title' => 'Suasana pembelajaran aktif di kelas', 'slug' => 'pembelajaran-aktif-di-kelas', 'summary' => 'Guru dan siswa berkolaborasi dalam suasana belajar yang interaktif.', 'image_path' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=360&q=80', 'date' => '2026-09-21', 'group' => 'carousel'],
            ['title' => 'Belajar bersama untuk memperkuat pemahaman materi', 'slug' => 'belajar-bersama', 'summary' => 'Diskusi kelompok membantu siswa saling berbagi cara dan pengalaman belajar.', 'image_path' => 'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?auto=format&fit=crop&w=360&q=80', 'date' => '2026-09-03', 'group' => 'carousel'],
            ['title' => 'Menghidupkan kebiasaan membaca di lingkungan sekolah', 'slug' => 'kebiasaan-membaca-di-sekolah', 'summary' => 'Kegiatan literasi mengajak siswa menjadikan membaca bagian dari keseharian.', 'image_path' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=360&q=80', 'date' => '2026-08-28', 'group' => 'carousel'],
            ['title' => 'Mengenal lingkungan sekolah melalui kegiatan orientasi', 'slug' => 'kegiatan-orientasi-sekolah', 'summary' => 'Siswa baru berkenalan dengan lingkungan belajar dan warga sekolah.', 'image_path' => 'https://images.unsplash.com/photo-1529390079861-591de354faf5?auto=format&fit=crop&w=360&q=80', 'date' => '2026-08-10', 'group' => 'carousel'],
            ['title' => 'Kebersamaan warga sekolah dalam peringatan kemerdekaan', 'slug' => 'peringatan-kemerdekaan', 'summary' => '', 'image_path' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=360&q=80', 'date' => '2026-08-17', 'group' => 'archive'],
            ['title' => 'Hasil praktik keahlian siswa hadir dalam pameran karya', 'slug' => 'pameran-karya-siswa', 'summary' => '', 'image_path' => null, 'date' => '2026-06-15', 'group' => 'archive'],
            ['title' => 'Menumbuhkan budaya membaca melalui kegiatan literasi', 'slug' => 'kegiatan-literasi-sekolah', 'summary' => '', 'image_path' => null, 'date' => '2026-04-20', 'group' => 'archive'],
        ];
    }
}
