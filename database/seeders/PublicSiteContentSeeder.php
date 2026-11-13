<?php

namespace Database\Seeders;

use App\Models\GalleryPhoto;
use App\Models\JobVacancy;
use App\Models\NewsArticle;
use App\Models\Program;
use App\Models\SiteSetting;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PublicSiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();
        $this->seedPrograms();
        $this->seedGallery();
        $this->seedDraftContent();
    }

    private function seedSettings(): void
    {
        $settings = array_replace(config('site-content.defaults', []), [
            'identity.school_name' => 'SMK Islamiyah',
            'identity.location' => 'Ciputat, Tangerang Selatan',
            'identity.logo' => '/images/logo/logo.PNG',
            'navbar.gallery.visible' => true,
            'home.hero.title' => 'Belajar, Berkarya, dan Menyiapkan Masa Depan',
            'home.hero.description' => 'Kenali lingkungan belajar, program keahlian, dan informasi resmi SMK Islamiyah Ciputat.',
            'home.hero.slogan' => 'Temukan bidang yang sesuai dengan minatmu.',
            'home.hero.image' => '/images/bagorundmotivtenagapendidik/bagorundtenagapendidik.png',
            'home.hero.image_alt' => 'Gedung SMK Islamiyah Ciputat',
            'home.hero.badge' => 'Ciputat, Tangerang Selatan',
            'home.hero.student_count' => '',
            'home.hero.primary_label' => 'Lihat Program Keahlian',
            'home.hero.primary_url' => '/#program-keahlian',
            'home.hero.secondary_label' => 'Tentang Sekolah',
            'home.hero.secondary_url' => '/tentang-kami/informasi-sekolah',
            'home.stats.visible' => false,
            'home.stats.student_count' => '',
            'home.stats.teacher_count' => '',
            'home.about.heading' => 'Ruang belajar untuk mengenali minat dan mengembangkan keterampilan.',
            'home.about.paragraph_1' => 'SMK Islamiyah Ciputat merupakan sekolah menengah kejuruan yang mendampingi peserta didik mengenali minat dan mempelajari bidang keahlian.',
            'home.about.paragraph_2' => 'Informasi program, kegiatan, dan layanan sekolah disajikan melalui halaman website ini.',
            'home.about.image' => '/images/10.jpeg',
            'home.about.image_alt' => 'Peserta didik berfoto bersama di lingkungan sekolah',
            'home.teachers.heading' => 'Para pendidik yang mendampingi perjalanan belajar siswa.',
            'home.teachers.description' => 'Informasi profil tenaga pendidik akan diperbarui melalui halaman ini.',
            'home.news.heading' => 'Berita dan Informasi Sekolah',
            'home.news.description' => 'Informasi kegiatan dan pengumuman resmi sekolah akan ditampilkan di halaman ini.',
            'home.news.video_1' => '',
            'home.news.video_2' => '',
            'home.bkk.heading' => 'Informasi Karier dan Tracer Study',
            'home.bkk.description' => 'Informasi layanan BKK dan peluang kerja yang telah dikonfirmasi akan ditampilkan di halaman ini.',
            'home.bkk.image' => '/images/bagorundmotivtenagapendidik/bagorundtenagapendidik.png',
            'home.admissions.year' => '',
            'home.admissions.heading' => 'Informasi Penerimaan Peserta Didik Baru',
            'home.admissions.description' => 'Persyaratan dan jadwal resmi akan diperbarui melalui halaman SPMB.',
            'home.admissions.steps' => '',
            'about.title' => 'Tentang SMK Islamiyah Ciputat',
            'about.description' => 'Informasi profil dan layanan SMK Islamiyah Ciputat.',
            'about.history' => '',
            'about.vision' => '',
            'about.mission' => '',
            'about.image' => '/images/bagorundmotivtenagapendidik/bagorundtenagapendidik.png',
            'teachers.title' => 'Tenaga Kependidikan',
            'teachers.description' => 'Profil tenaga pendidik akan ditampilkan setelah data resmi dilengkapi.',
            'bkk.title' => 'Informasi Lowongan Kerja',
            'bkk.description' => 'Informasi lowongan akan ditampilkan setelah dikonfirmasi oleh perusahaan atau mitra.',
            'bkk.tracer_title' => 'Tracer Study',
            'bkk.tracer_description' => 'Informasi pendataan alumni akan diperbarui melalui halaman ini.',
            'bkk.tracer_url' => '',
            'bkk.cooperation' => '',
            'spmb.open' => false,
            'spmb.year' => '',
            'spmb.title' => 'Penerimaan Peserta Didik Baru',
            'spmb.description' => 'Informasi pendaftaran akan diperbarui setelah jadwal resmi tersedia.',
            'spmb.schedule' => '',
            'spmb.requirements' => '',
            'spmb.steps' => '',
            'spmb.fee' => '',
            'spmb.form_url' => '',
            'spmb.contact' => '',
            'footer.description' => 'Website informasi SMK Islamiyah Ciputat. Informasi resmi sekolah akan diperbarui secara berkala.',
            'seo.title' => 'SMK Islamiyah Ciputat',
            'seo.description' => 'Informasi program keahlian, tenaga kependidikan, kegiatan, dan layanan SMK Islamiyah Ciputat.',
            'seo.keywords' => 'SMK Islamiyah Ciputat, sekolah kejuruan, Ciputat',
            'seo.og_image' => '/images/bagorundmotivtenagapendidik/bagorundtenagapendidik.png',
        ]);

        foreach ($settings as $key => $value) {
            SiteSetting::query()->firstOrCreate(
                ['key' => $key],
                [
                    'group' => Str::before($key, '.'),
                    'value' => json_encode($value, JSON_THROW_ON_ERROR),
                ],
            );
        }
    }

    private function seedPrograms(): void
    {
        $programs = [
            [
                'title' => 'Kuliner',
                'slug' => 'kuliner',
                'route_key' => 'kuliner',
                'abbreviation' => 'KULINER',
                'summary' => 'Mengenal pengolahan makanan, penyajian, dan dasar keterampilan di bidang kuliner.',
                'body' => 'Program Kuliner menjadi ruang belajar untuk mengenal bahan pangan, proses pengolahan, penyajian, serta kebiasaan kerja yang tertib dan bersih.',
                'image_path' => '/images/2.jpg',
                'primary_competence' => 'Pengolahan dan penyajian makanan.',
                'career_opportunities' => 'Bidang kuliner dan layanan makanan, sesuai kompetensi yang dikembangkan.',
                'advantages' => "Mengenal bahan dan teknik dasar pengolahan makanan\nMempelajari penyajian makanan\nMelatih ketelitian dan kebersihan kerja",
            ],
            [
                'title' => 'Desain Komunikasi Visual',
                'slug' => 'desain-komunikasi-visual',
                'route_key' => 'dkv',
                'abbreviation' => 'DKV',
                'summary' => 'Mengembangkan kreativitas dalam desain grafis dan komunikasi visual.',
                'body' => 'Program Desain Komunikasi Visual mengajak peserta didik mengembangkan gagasan dan menyampaikan pesan melalui karya visual.',
                'image_path' => '/images/3.jpg',
                'primary_competence' => 'Perancangan karya dan pesan visual.',
                'career_opportunities' => 'Bidang desain grafis dan komunikasi visual, sesuai kompetensi yang dikembangkan.',
                'advantages' => "Mengembangkan ide kreatif\nMengenal prinsip desain dan komunikasi visual\nMenyusun karya visual untuk menyampaikan pesan",
            ],
            [
                'title' => 'Teknik Komputer dan Jaringan',
                'slug' => 'teknik-komputer-dan-jaringan',
                'route_key' => 'tkj',
                'abbreviation' => 'TKJ',
                'summary' => 'Mengenal komputer, jaringan, dan teknologi informasi.',
                'body' => 'Program Teknik Komputer dan Jaringan memperkenalkan konsep komputer dan jaringan sebagai dasar untuk memahami teknologi informasi.',
                'image_path' => '/images/4.jpg',
                'primary_competence' => 'Dasar komputer dan jaringan.',
                'career_opportunities' => 'Bidang dukungan teknologi informasi dan jaringan, sesuai kompetensi yang dikembangkan.',
                'advantages' => "Mengenal komponen dan sistem komputer\nMemahami dasar jaringan komputer\nMelatih pemecahan masalah secara terstruktur",
            ],
            [
                'title' => 'Akuntansi',
                'slug' => 'akuntansi',
                'route_key' => 'ak',
                'abbreviation' => 'AK',
                'summary' => 'Mempelajari pencatatan transaksi dan dasar pengelolaan keuangan.',
                'body' => 'Program Akuntansi mengenalkan pencatatan transaksi, pengelompokan informasi keuangan, dan ketelitian dalam mengolah data.',
                'image_path' => '/images/5.jpg',
                'primary_competence' => 'Pencatatan dan pengolahan informasi keuangan.',
                'career_opportunities' => 'Bidang administrasi keuangan dan akuntansi, sesuai kompetensi yang dikembangkan.',
                'advantages' => "Mengenal pencatatan transaksi\nMempelajari dasar pengelolaan keuangan\nMelatih ketelitian dan tanggung jawab",
            ],
            [
                'title' => 'Manajemen Perkantoran',
                'slug' => 'manajemen-perkantoran',
                'route_key' => 'mp',
                'abbreviation' => 'MP',
                'summary' => 'Mengenal administrasi, pengelolaan dokumen, dan komunikasi perkantoran.',
                'body' => 'Program Manajemen Perkantoran memperkenalkan kegiatan administrasi dan pengelolaan informasi yang mendukung pekerjaan kantor.',
                'image_path' => null,
                'primary_competence' => 'Administrasi dan pengelolaan informasi perkantoran.',
                'career_opportunities' => 'Bidang administrasi dan layanan perkantoran, sesuai kompetensi yang dikembangkan.',
                'advantages' => "Mengenal proses administrasi\nMempelajari pengelolaan dokumen dan informasi\nMelatih komunikasi dan kerapian kerja",
            ],
            [
                'title' => 'Bisnis Digital',
                'slug' => 'bisnis-digital',
                'route_key' => 'bd',
                'abbreviation' => 'BD',
                'summary' => 'Mengenal kegiatan bisnis dan pemanfaatan teknologi digital.',
                'body' => 'Program Bisnis Digital memperkenalkan dasar kegiatan bisnis serta pemanfaatan teknologi digital untuk menyampaikan informasi dan menjangkau pelanggan.',
                'image_path' => '/images/1.jpg',
                'primary_competence' => 'Dasar bisnis dan pemanfaatan teknologi digital.',
                'career_opportunities' => 'Bidang bisnis dan pemasaran digital, sesuai kompetensi yang dikembangkan.',
                'advantages' => "Mengenal konsep dasar bisnis\nMemahami pemanfaatan media digital\nMengembangkan ide dan komunikasi pemasaran",
            ],
        ];

        foreach ($programs as $index => $program) {
            Program::query()->firstOrCreate(
                ['route_key' => $program['route_key']],
                [
                    'title' => $program['title'],
                    'slug' => $program['slug'],
                    'route_key' => $program['route_key'],
                    'summary' => $program['summary'],
                    'body' => $program['body'],
                    'category' => 'Program Keahlian',
                    'image_path' => $program['image_path'],
                    'status' => 'published',
                    'featured' => false,
                    'sort_order' => $index + 1,
                    'published_at' => null,
                    'meta_title' => $program['title'].' | SMK Islamiyah Ciputat',
                    'meta_description' => $program['summary'],
                    'metadata' => [
                        'abbreviation' => $program['abbreviation'],
                        'icon' => 'graduation-cap',
                        'primary_competence' => $program['primary_competence'],
                        'facilities' => '',
                        'career_opportunities' => $program['career_opportunities'],
                        'advantages' => $program['advantages'],
                    ],
                ],
            );
        }
    }

    private function seedGallery(): void
    {
        $photos = [
            [
                'title' => 'Kebersamaan di lingkungan sekolah',
                'slug' => 'kebersamaan-di-lingkungan-sekolah',
                'summary' => 'Peserta didik berfoto bersama di lingkungan sekolah.',
                'image_path' => '/images/10.jpeg',
                'sort_order' => 1,
            ],
            [
                'title' => 'Gedung SMK Islamiyah Ciputat',
                'slug' => 'gedung-smk-islamiyah-ciputat',
                'summary' => 'Tampak gedung SMK Islamiyah Ciputat.',
                'image_path' => '/images/bagorundmotivtenagapendidik/bagorundtenagapendidik.png',
                'sort_order' => 2,
            ],
        ];

        foreach ($photos as $photo) {
            GalleryPhoto::query()->firstOrCreate(
                ['slug' => $photo['slug']],
                [
                    ...$photo,
                    'category' => 'Lingkungan Sekolah',
                    'status' => 'published',
                    'featured' => false,
                    'published_at' => null,
                    'metadata' => [],
                ],
            );
        }
    }

    private function seedDraftContent(): void
    {
        NewsArticle::query()->firstOrCreate(
            ['slug' => 'draf-berita-sekolah'],
            [
                'title' => 'DRAF — Berita Sekolah',
                'summary' => 'Lengkapi ringkasan berdasarkan kegiatan atau pengumuman resmi yang telah terverifikasi.',
                'body' => 'Draf awal. Ganti isi ini dengan tanggal, lokasi, dan keterangan kegiatan yang benar sebelum mengubah status menjadi Published.',
                'category' => 'Informasi Sekolah',
                'status' => 'draft',
                'featured' => false,
                'sort_order' => 0,
                'metadata' => ['homepage_group' => 'news_only'],
            ],
        );

        JobVacancy::query()->firstOrCreate(
            ['slug' => 'draf-informasi-lowongan-bkk'],
            [
                'title' => 'DRAF — Informasi Lowongan BKK',
                'summary' => 'Lengkapi setelah menerima informasi resmi dari perusahaan atau mitra.',
                'body' => 'Draf awal. Ganti nama perusahaan, posisi, persyaratan, dan tautan pendaftaran dengan informasi yang telah dikonfirmasi sebelum diterbitkan.',
                'category' => 'Bursa Kerja Khusus',
                'status' => 'draft',
                'featured' => false,
                'sort_order' => 0,
                'metadata' => [
                    'company' => 'Lengkapi nama perusahaan',
                    'position' => 'Lengkapi posisi pekerjaan',
                    'requirements' => '',
                ],
            ],
        );

        Teacher::query()->firstOrCreate(
            ['slug' => 'draf-profil-tenaga-pendidik'],
            [
                'name' => 'DRAF — Lengkapi Profil Tenaga Pendidik',
                'position' => 'Lengkapi jabatan atau mata pelajaran',
                'category' => 'Guru',
                'summary' => 'Draf awal. Ganti dengan nama, jabatan, dan foto resmi sebelum mengubah status menjadi Published.',
                'status' => 'draft',
                'sort_order' => 0,
                'published_at' => null,
            ],
        );
    }
}
