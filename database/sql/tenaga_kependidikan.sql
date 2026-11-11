-- Data contoh awal tenaga kependidikan SMK Islamiyah Ciputat.
-- Target: database MySQL `smkiciputat` (sesuai DB_DATABASE di .env).
-- Semua baris diberi nama DATA CONTOH. Ganti sebelum digunakan sebagai data nyata.

USE `smkiciputat`;

CREATE TABLE IF NOT EXISTS `teachers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `position` VARCHAR(255) NOT NULL,
    `education` VARCHAR(255) NULL,
    `category` VARCHAR(255) NULL,
    `summary` TEXT NULL,
    `image_path` VARCHAR(255) NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'published',
    `sort_order` INT NOT NULL DEFAULT 0,
    `published_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `teachers_slug_unique` (`slug`),
    KEY `teachers_status_published_at_sort_order_index` (`status`, `published_at`, `sort_order`),
    KEY `teachers_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

INSERT INTO `teachers` (
    `name`,
    `slug`,
    `position`,
    `education`,
    `category`,
    `summary`,
    `image_path`,
    `status`,
    `sort_order`,
    `published_at`,
    `created_at`,
    `updated_at`
)
SELECT
    samples.`name`,
    samples.`slug`,
    samples.`position`,
    samples.`education`,
    samples.`category`,
    samples.`summary`,
    samples.`image_path`,
    samples.`status`,
    samples.`sort_order`,
    samples.`published_at`,
    samples.`created_at`,
    samples.`updated_at`
FROM (
    SELECT
        'DATA CONTOH 01' AS `name`,
        'data-contoh-tenaga-kependidikan-01' AS `slug`,
        'Jabatan Contoh 01' AS `position`,
        NULL AS `education`,
        'DATA CONTOH' AS `category`,
        'DATA CONTOH - Ganti dengan deskripsi profil yang sebenarnya.' AS `summary`,
        NULL AS `image_path`,
        'published' AS `status`,
        1 AS `sort_order`,
        NULL AS `published_at`,
        CURRENT_TIMESTAMP AS `created_at`,
        CURRENT_TIMESTAMP AS `updated_at`
    UNION ALL SELECT
        'DATA CONTOH 02', 'data-contoh-tenaga-kependidikan-02', 'Jabatan Contoh 02',
        NULL, 'DATA CONTOH', 'DATA CONTOH - Ganti dengan deskripsi profil yang sebenarnya.',
        NULL, 'published', 2, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    UNION ALL SELECT
        'DATA CONTOH 03', 'data-contoh-tenaga-kependidikan-03', 'Jabatan Contoh 03',
        NULL, 'DATA CONTOH', 'DATA CONTOH - Ganti dengan deskripsi profil yang sebenarnya.',
        NULL, 'published', 3, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    UNION ALL SELECT
        'DATA CONTOH 04', 'data-contoh-tenaga-kependidikan-04', 'Jabatan Contoh 04',
        NULL, 'DATA CONTOH', 'DATA CONTOH - Ganti dengan deskripsi profil yang sebenarnya.',
        NULL, 'published', 4, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    UNION ALL SELECT
        'DATA CONTOH 05', 'data-contoh-tenaga-kependidikan-05', 'Jabatan Contoh 05',
        NULL, 'DATA CONTOH', 'DATA CONTOH - Ganti dengan deskripsi profil yang sebenarnya.',
        NULL, 'published', 5, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    UNION ALL SELECT
        'DATA CONTOH 06', 'data-contoh-tenaga-kependidikan-06', 'Jabatan Contoh 06',
        NULL, 'DATA CONTOH', 'DATA CONTOH - Ganti dengan deskripsi profil yang sebenarnya.',
        NULL, 'published', 6, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    UNION ALL SELECT
        'DATA CONTOH 07', 'data-contoh-tenaga-kependidikan-07', 'Jabatan Contoh 07',
        NULL, 'DATA CONTOH', 'DATA CONTOH - Ganti dengan deskripsi profil yang sebenarnya.',
        NULL, 'published', 7, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    UNION ALL SELECT
        'DATA CONTOH 08', 'data-contoh-tenaga-kependidikan-08', 'Jabatan Contoh 08',
        NULL, 'DATA CONTOH', 'DATA CONTOH - Ganti dengan deskripsi profil yang sebenarnya.',
        NULL, 'published', 8, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    UNION ALL SELECT
        'DATA CONTOH 09', 'data-contoh-tenaga-kependidikan-09', 'Jabatan Contoh 09',
        NULL, 'DATA CONTOH', 'DATA CONTOH - Ganti dengan deskripsi profil yang sebenarnya.',
        NULL, 'published', 9, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
    UNION ALL SELECT
        'DATA CONTOH 10', 'data-contoh-tenaga-kependidikan-10', 'Jabatan Contoh 10',
        NULL, 'DATA CONTOH', 'DATA CONTOH - Ganti dengan deskripsi profil yang sebenarnya.',
        NULL, 'published', 10, NULL, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
) AS samples
WHERE NOT EXISTS (
    SELECT 1
    FROM `teachers` AS existing
    WHERE existing.`slug` = samples.`slug`
);

COMMIT;
