-- Data uji untuk mengisi modul konten admin dan halaman publik.
-- Hanya INSERT data yang belum ada; tidak mengubah schema, akun admin, atau data lama.
-- JANGAN jalankan di production: record DATA UJI akan tampil pada website publik.
-- Koneksi teachers pada konfigurasi saat ini menggunakan database yang sama: smkiciputat.

USE `smkiciputat`;

START TRANSACTION;

INSERT INTO `programs` (
    `title`, `slug`, `route_key`, `summary`, `body`, `category`, `image_path`,
    `status`, `featured`, `sort_order`, `published_at`, `meta_title`,
    `meta_description`, `metadata`, `created_at`, `updated_at`
)
SELECT
    sample.`title`, sample.`slug`, sample.`route_key`, sample.`summary`, sample.`body`,
    sample.`category`, sample.`image_path`, sample.`status`, sample.`featured`,
    sample.`sort_order`, sample.`published_at`, sample.`meta_title`,
    sample.`meta_description`, sample.`metadata`, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT
        'DATA UJI - Program Keahlian' AS `title`,
        'data-uji-program-keahlian' AS `slug`,
        'data-uji-program-keahlian' AS `route_key`,
        'Ringkasan uji untuk kartu program keahlian.' AS `summary`,
        'Ini adalah konten uji untuk memeriksa tampilan detail program, kompetensi, fasilitas, dan peluang karier. Ganti dengan informasi resmi sebelum digunakan di production.' AS `body`,
        'Program Keahlian' AS `category`,
        '/images/2.jpg' AS `image_path`,
        'published' AS `status`,
        0 AS `featured`,
        -1 AS `sort_order`,
        CURRENT_TIMESTAMP AS `published_at`,
        'DATA UJI - Program Keahlian | SMK Islamiyah Ciputat' AS `meta_title`,
        'Konten uji halaman program keahlian.' AS `meta_description`,
        JSON_OBJECT(
            'abbreviation', 'UJI',
            'icon', 'graduation-cap',
            'primary_competence', 'Kompetensi simulasi',
            'facilities', 'Fasilitas simulasi',
            'career_opportunities', 'Informasi karier simulasi',
            'advantages', CONVERT(
                CONCAT('Kompetensi uji satu', CHAR(10), 'Kompetensi uji dua')
                USING utf8mb4
            )
        ) AS `metadata`
) AS sample
WHERE NOT EXISTS (
    SELECT 1 FROM `programs` AS existing WHERE existing.`slug` = sample.`slug`
);

INSERT INTO `news_articles` (
    `title`, `slug`, `summary`, `body`, `category`, `image_path`, `status`,
    `featured`, `sort_order`, `published_at`, `meta_title`, `meta_description`,
    `metadata`, `created_at`, `updated_at`
)
SELECT
    sample.`title`, sample.`slug`, sample.`summary`, sample.`body`, sample.`category`,
    sample.`image_path`, sample.`status`, sample.`featured`, sample.`sort_order`,
    sample.`published_at`, sample.`meta_title`, sample.`meta_description`,
    sample.`metadata`, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT
        'DATA UJI - Kegiatan Sekolah' AS `title`,
        'data-uji-kegiatan-sekolah' AS `slug`,
        'Berita uji untuk memeriksa kartu berita dan halaman detail.' AS `summary`,
        'Konten ini hanya untuk pengujian tampilan dan alur berita. Ganti dengan tanggal, lokasi, dan keterangan kegiatan yang telah diverifikasi sebelum diterbitkan sebagai informasi resmi.' AS `body`,
        'Informasi Uji' AS `category`,
        '/images/10.jpeg' AS `image_path`,
        'published' AS `status`,
        0 AS `featured`,
        -1 AS `sort_order`,
        CURRENT_TIMESTAMP AS `published_at`,
        'DATA UJI - Kegiatan Sekolah' AS `meta_title`,
        'Berita uji SMK Islamiyah Ciputat.' AS `meta_description`,
        '{"homepage_group":"carousel"}' AS `metadata`
) AS sample
WHERE NOT EXISTS (
    SELECT 1 FROM `news_articles` AS existing WHERE existing.`slug` = sample.`slug`
);

INSERT INTO `principal_welcomes` (
    `title`, `slug`, `summary`, `body`, `category`, `image_path`, `status`,
    `featured`, `sort_order`, `published_at`, `meta_title`, `meta_description`,
    `metadata`, `created_at`, `updated_at`
)
SELECT
    sample.`title`, sample.`slug`, sample.`summary`, sample.`body`, sample.`category`,
    sample.`image_path`, sample.`status`, sample.`featured`, sample.`sort_order`,
    sample.`published_at`, sample.`meta_title`, sample.`meta_description`,
    sample.`metadata`, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT
        'DATA UJI - Sambutan Kepala Sekolah' AS `title`,
        'data-uji-sambutan-kepala-sekolah' AS `slug`,
        'Assalamu alaikum. Ini adalah salam pembuka untuk pengujian.' AS `summary`,
        'Isi sambutan ini merupakan data uji, bukan pernyataan kepala sekolah. Ganti dengan sambutan yang telah disetujui sebelum digunakan di production.' AS `body`,
        'Kepala Sekolah - Data Uji' AS `category`,
        NULL AS `image_path`,
        'published' AS `status`,
        0 AS `featured`,
        -1 AS `sort_order`,
        CURRENT_TIMESTAMP AS `published_at`,
        'DATA UJI - Sambutan Kepala Sekolah' AS `meta_title`,
        'Konten uji sambutan kepala sekolah.' AS `meta_description`,
        '{"name":"DATA UJI - Kepala Sekolah","description":"Profil simulasi untuk pengujian tampilan."}' AS `metadata`
) AS sample
WHERE NOT EXISTS (
    SELECT 1 FROM `principal_welcomes` AS existing WHERE existing.`slug` = sample.`slug`
);

INSERT INTO `job_vacancies` (
    `title`, `slug`, `summary`, `body`, `category`, `image_path`, `status`,
    `featured`, `sort_order`, `published_at`, `meta_title`, `meta_description`,
    `metadata`, `created_at`, `updated_at`
)
SELECT
    sample.`title`, sample.`slug`, sample.`summary`, sample.`body`, sample.`category`,
    sample.`image_path`, sample.`status`, sample.`featured`, sample.`sort_order`,
    sample.`published_at`, sample.`meta_title`, sample.`meta_description`,
    sample.`metadata`, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT
        'DATA UJI - Lowongan Pekerjaan' AS `title`,
        'data-uji-lowongan-pekerjaan' AS `slug`,
        'Informasi lowongan simulasi untuk menguji tampilan BKK.' AS `summary`,
        'Ini adalah lowongan fiktif untuk pengujian. Jangan menghubungi perusahaan atau mengirim lamaran berdasarkan data ini.' AS `body`,
        'Bursa Kerja Khusus - Data Uji' AS `category`,
        NULL AS `image_path`,
        'published' AS `status`,
        0 AS `featured`,
        -1 AS `sort_order`,
        CURRENT_TIMESTAMP AS `published_at`,
        'DATA UJI - Lowongan Pekerjaan' AS `meta_title`,
        'Lowongan simulasi untuk pengujian website.' AS `meta_description`,
        '{"company":"Perusahaan Simulasi (Data Uji)","position":"Posisi Simulasi","closing_date":"","application_url":"","requirements":"Data uji saja; tidak menerima lamaran."}' AS `metadata`
) AS sample
WHERE NOT EXISTS (
    SELECT 1 FROM `job_vacancies` AS existing WHERE existing.`slug` = sample.`slug`
);

INSERT INTO `gallery_photos` (
    `title`, `slug`, `summary`, `body`, `category`, `image_path`, `status`,
    `featured`, `sort_order`, `published_at`, `meta_title`, `meta_description`,
    `metadata`, `created_at`, `updated_at`
)
SELECT
    sample.`title`, sample.`slug`, sample.`summary`, sample.`body`, sample.`category`,
    sample.`image_path`, sample.`status`, sample.`featured`, sample.`sort_order`,
    sample.`published_at`, sample.`meta_title`, sample.`meta_description`,
    sample.`metadata`, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT
        'DATA UJI - Galeri Sekolah' AS `title`,
        'data-uji-galeri-sekolah' AS `slug`,
        'Entri uji untuk memeriksa daftar dan detail galeri.' AS `summary`,
        NULL AS `body`,
        'Galeri Uji' AS `category`,
        '/images/10.jpeg' AS `image_path`,
        'published' AS `status`,
        0 AS `featured`,
        -1 AS `sort_order`,
        CURRENT_TIMESTAMP AS `published_at`,
        'DATA UJI - Galeri Sekolah' AS `meta_title`,
        'Entri galeri uji.' AS `meta_description`,
        '{"location":"Lokasi simulasi","program_route_key":"data-uji-program-keahlian"}' AS `metadata`
) AS sample
WHERE NOT EXISTS (
    SELECT 1 FROM `gallery_photos` AS existing WHERE existing.`slug` = sample.`slug`
);

INSERT INTO `teachers` (
    `name`, `slug`, `position`, `education`, `category`, `summary`, `image_path`,
    `status`, `sort_order`, `published_at`, `created_at`, `updated_at`
)
SELECT
    sample.`name`, sample.`slug`, sample.`position`, sample.`education`,
    sample.`category`, sample.`summary`, sample.`image_path`, sample.`status`,
    sample.`sort_order`, sample.`published_at`, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT
        'DATA UJI - Guru Matematika' AS `name`,
        'data-uji-guru-matematika' AS `slug`,
        'Guru Matematika - Data Uji' AS `position`,
        'Pendidikan Simulasi' AS `education`,
        'Guru' AS `category`,
        'Profil simulasi untuk menguji kartu dan popup tenaga kependidikan.' AS `summary`,
        NULL AS `image_path`,
        'published' AS `status`,
        -3 AS `sort_order`,
        CURRENT_TIMESTAMP AS `published_at`
    UNION ALL SELECT
        'DATA UJI - Guru Bahasa', 'data-uji-guru-bahasa',
        'Guru Bahasa - Data Uji', 'Pendidikan Simulasi', 'Guru',
        'Profil simulasi tanpa foto untuk menguji tampilan data yang tidak lengkap.',
        NULL, 'published', -2, CURRENT_TIMESTAMP
    UNION ALL SELECT
        'DATA UJI - Tenaga Administrasi', 'data-uji-tenaga-administrasi',
        'Tenaga Administrasi - Data Uji', 'Pendidikan Simulasi', 'Tenaga Kependidikan',
        'Profil simulasi untuk menguji kategori tenaga kependidikan.',
        NULL, 'published', -1, CURRENT_TIMESTAMP
) AS sample
WHERE NOT EXISTS (
    SELECT 1 FROM `teachers` AS existing WHERE existing.`slug` = sample.`slug`
);

INSERT INTO `site_settings` (`key`, `group`, `value`, `created_at`, `updated_at`)
SELECT sample.`key`, sample.`group`, sample.`value`, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT 'about.history' AS `key`, 'about' AS `group`,
        JSON_QUOTE('DATA UJI - Sejarah sekolah untuk pengujian halaman Tentang Kami.') AS `value`
    UNION ALL SELECT 'about.vision', 'about',
        JSON_QUOTE('DATA UJI - Visi sekolah. Ganti dengan visi resmi sebelum digunakan di production.')
    UNION ALL SELECT 'about.mission', 'about',
        JSON_QUOTE(CONVERT(
            CONCAT('DATA UJI - Misi pertama', CHAR(10), 'DATA UJI - Misi kedua')
            USING utf8mb4
        ))
    UNION ALL SELECT 'bkk.cooperation', 'bkk',
        JSON_QUOTE('DATA UJI - Informasi kerja sama industri simulasi.')
    UNION ALL SELECT 'spmb.schedule', 'spmb',
        JSON_QUOTE('DATA UJI - Jadwal pendaftaran simulasi.')
    UNION ALL SELECT 'spmb.requirements', 'spmb',
        JSON_QUOTE('DATA UJI - Persyaratan simulasi.')
    UNION ALL SELECT 'spmb.steps', 'spmb',
        JSON_QUOTE(CONVERT(
            CONCAT(
                'DATA UJI - Isi formulir', CHAR(10),
                'DATA UJI - Lengkapi data', CHAR(10),
                'DATA UJI - Selesaikan pendaftaran'
            )
            USING utf8mb4
        ))
    UNION ALL SELECT 'spmb.fee', 'spmb',
        JSON_QUOTE('DATA UJI - Informasi biaya simulasi.')
    UNION ALL SELECT 'spmb.contact', 'spmb',
        JSON_QUOTE('Kontak Simulasi - Data Uji')
) AS sample
WHERE NOT EXISTS (
    SELECT 1 FROM `site_settings` AS existing WHERE existing.`key` = sample.`key`
);

INSERT INTO `content_activities` (
    `admin_id`, `content_type`, `content_id`, `action`, `title`, `created_at`, `updated_at`
)
SELECT
    (SELECT MIN(`id`) FROM `admins`), sample.`content_type`, sample.`content_id`,
    'created', sample.`title`, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM (
    SELECT 'program' AS `content_type`, `id` AS `content_id`, `title`
    FROM `programs` WHERE `slug` = 'data-uji-program-keahlian'
    UNION ALL
    SELECT 'news', `id`, `title`
    FROM `news_articles` WHERE `slug` = 'data-uji-kegiatan-sekolah'
    UNION ALL
    SELECT 'principal_welcome', `id`, `title`
    FROM `principal_welcomes` WHERE `slug` = 'data-uji-sambutan-kepala-sekolah'
    UNION ALL
    SELECT 'job', `id`, `title`
    FROM `job_vacancies` WHERE `slug` = 'data-uji-lowongan-pekerjaan'
    UNION ALL
    SELECT 'gallery', `id`, `title`
    FROM `gallery_photos` WHERE `slug` = 'data-uji-galeri-sekolah'
    UNION ALL
    SELECT 'teacher', `id`, `name`
    FROM `teachers`
    WHERE `slug` IN (
        'data-uji-guru-matematika',
        'data-uji-guru-bahasa',
        'data-uji-tenaga-administrasi'
    )
) AS sample
WHERE NOT EXISTS (
    SELECT 1
    FROM `content_activities` AS existing
    WHERE existing.`content_type` = sample.`content_type`
        AND existing.`content_id` = sample.`content_id`
        AND existing.`action` = 'created'
        AND existing.`title` = sample.`title`
);

COMMIT;
