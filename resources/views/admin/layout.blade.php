<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('title', 'Admin CMS') | SMK Islamiyah Ciputat</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <input class="admin-drawer-toggle" type="checkbox" id="admin-drawer-toggle" aria-label="Buka navigasi admin">

    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="admin-brand">
            <img src="{{ asset('images/logo/logo.PNG') }}" alt="" class="admin-brand-logo">
            <span>
                <strong>SMK Islamiyah</strong>
                <small>Content Management</small>
            </span>
        </a>

        <nav class="admin-navigation" aria-label="Navigasi admin">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                <span aria-hidden="true">⌂</span> Dashboard
            </a>
            <p class="admin-nav-caption">Konten Website</p>
            <a href="{{ route('admin.content.index', 'programs') }}" class="admin-nav-link {{ request()->route('type') === 'programs' ? 'is-active' : '' }}"><span aria-hidden="true">▦</span> Program Keahlian</a>
            <a href="{{ route('admin.teachers.index') }}" class="admin-nav-link {{ request()->routeIs('admin.teachers.*') ? 'is-active' : '' }}"><span aria-hidden="true">♙</span> Tenaga Kependidikan</a>
            <a href="{{ route('admin.content.index', 'principal-welcomes') }}" class="admin-nav-link {{ request()->route('type') === 'principal-welcomes' ? 'is-active' : '' }}"><span aria-hidden="true">▤</span> Sambutan Kepala Sekolah</a>
            <a href="{{ route('admin.content.index', 'news') }}" class="admin-nav-link {{ request()->route('type') === 'news' ? 'is-active' : '' }}"><span aria-hidden="true">▤</span> Berita</a>
            <a href="{{ route('admin.content.index', 'jobs') }}" class="admin-nav-link {{ request()->route('type') === 'jobs' ? 'is-active' : '' }}"><span aria-hidden="true">▣</span> Lowongan BKK</a>
            <a href="{{ route('admin.content.index', 'gallery') }}" class="admin-nav-link {{ request()->route('type') === 'gallery' ? 'is-active' : '' }}"><span aria-hidden="true">▧</span> Galeri</a>
            @if (! auth('admin')->user()?->isEditor())
                <p class="admin-nav-caption">Pengelolaan</p>
                <a href="{{ route('admin.media.index') }}" class="admin-nav-link {{ request()->routeIs('admin.media.*') ? 'is-active' : '' }}"><span aria-hidden="true">▣</span> Media Manager</a>
                <a href="{{ route('admin.settings.index') }}" class="admin-nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}"><span aria-hidden="true">⚙</span> Pengaturan Website</a>
            @endif
            @if (auth('admin')->user()?->isSuperAdmin())
                <a href="{{ route('admin.admins.index') }}" class="admin-nav-link {{ request()->routeIs('admin.admins.*') ? 'is-active' : '' }}"><span aria-hidden="true">♧</span> Manajemen Admin</a>
            @elseif (! auth('admin')->user()?->isEditor())
                <a href="{{ route('admin.admins.edit', auth('admin')->user()) }}" class="admin-nav-link {{ request()->routeIs('admin.admins.*') ? 'is-active' : '' }}"><span aria-hidden="true">♧</span> Profil Saya</a>
            @endif
        </nav>

        <div class="admin-sidebar-bottom">
            <a class="admin-website-link" href="{{ route('home') }}" target="_blank" rel="noopener">Lihat website <span aria-hidden="true">↗</span></a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="admin-logout" type="submit"><span aria-hidden="true">↪</span> Keluar</button>
            </form>
        </div>
    </aside>

    <label class="admin-backdrop" for="admin-drawer-toggle" aria-label="Tutup navigasi admin"></label>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar-title">
                <label class="admin-menu-button" for="admin-drawer-toggle" aria-label="Buka menu">☰</label>
                <div>
                    <p>Admin Panel <span>/</span> @yield('breadcrumb', 'Dashboard')</p>
                    <h1>@yield('page-title', 'Dashboard')</h1>
                </div>
            </div>
            <div class="admin-account">
                <span class="admin-avatar">{{ mb_substr(auth('admin')->user()->name, 0, 1) }}</span>
                <span>
                    <strong>{{ auth('admin')->user()->name }}</strong>
                    <small>{{ auth('admin')->user()->roleLabel() }} ({{ auth('admin')->user()->username }})</small>
                </span>
            </div>
        </header>

        <div class="admin-content">
            @if (session('success'))
                <div class="admin-alert admin-alert-success" role="status">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="admin-alert admin-alert-error" role="alert">
                    <strong>Periksa kembali data yang dimasukkan.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    <script>
        document.querySelectorAll('[data-confirm]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!window.confirm(form.dataset.confirm)) {
                    event.preventDefault();
                }
            });
        });

        (function () {
            const qs = (root, sel) => (root || document).querySelector(sel);
            const qsa = (root, sel) => Array.from((root || document).querySelectorAll(sel));

            qsa(document, '[data-bulk-root]').forEach((root) => {
                const checkboxes = () => qsa(root, 'input[data-bulk-item]:not([disabled])');
                const checked = () => checkboxes().filter((cb) => cb.checked);
                const countEl = qs(root, '[data-bulk-count]');
                const bar = qs(root, '[data-bulk-bar]');
                const selectAllPage = qs(root, '[data-bulk-select-page]');
                const selectAllCategory = qs(root, '[data-bulk-select-all-category]');
                const clearBtn = qs(root, '[data-bulk-clear]');
                const deleteBtn = qs(root, '[data-bulk-delete]');
                const bulkForm = qs(root, 'form[data-bulk-form]');
                const allInput = bulkForm ? qs(bulkForm, 'input[name="select_all"]') : null;
                let allCategory = false;

                function sync() {
                    const n = checked().length;
                    if (countEl) countEl.textContent = n;
                    if (bar) bar.hidden = n === 0 && !allCategory;
                    if (deleteBtn) deleteBtn.disabled = n === 0 && !allCategory;
                    if (selectAllPage) selectAllPage.checked = checkboxes().length > 0 && n === checkboxes().length;
                    if (selectAllCategory) selectAllCategory.classList.toggle('is-active', allCategory);
                }

                checkboxes().forEach((cb) => cb.addEventListener('change', () => { if (!cb.checked) allCategory = false; if (allInput) allInput.value = '0'; sync(); }));
                if (selectAllPage) selectAllPage.addEventListener('change', () => { allCategory = false; if (allInput) allInput.value = '0'; checkboxes().forEach((cb) => cb.checked = selectAllPage.checked); sync(); });
                if (selectAllCategory) selectAllCategory.addEventListener('click', () => { allCategory = !allCategory; if (allInput) allInput.value = allCategory ? '1' : '0'; if (allCategory) checkboxes().forEach((cb) => cb.checked = true); sync(); });
                if (clearBtn) clearBtn.addEventListener('click', () => { allCategory = false; if (allInput) allInput.value = '0'; checkboxes().forEach((cb) => cb.checked = false); if (selectAllPage) selectAllPage.checked = false; sync(); });
                if (deleteBtn) deleteBtn.addEventListener('click', () => openBulkDelete(root, { allCategory, ids: checked().map((cb) => cb.value), names: checked().map((cb) => cb.dataset.name || ('ID ' + cb.value)) }));
                sync();
            });

            function openModal(modal) { if (modal) { modal.hidden = false; document.body.style.overflow = 'hidden'; } }
            function closeModal(modal) { if (modal) { modal.hidden = true; if (!document.querySelector('.admin-modal:not([hidden])')) document.body.style.overflow = ''; } }
            qsa(document, '[data-modal-close]').forEach((el) => el.addEventListener('click', () => closeModal(el.closest('.admin-modal'))));
            qsa(document, '.admin-modal').forEach((modal) => modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(modal); }));

            function openBulkDelete(root, state) {
                const modal = qs(document, '#admin-bulk-delete-modal');
                const form = qs(document, '#admin-bulk-delete-form');
                const namesEl = qs(document, '#admin-bulk-delete-names');
                const summaryEl = qs(document, '#admin-bulk-delete-summary');
                const countEl = qs(document, '#admin-bulk-delete-count');
                const idsWrap = qs(document, '#admin-bulk-delete-ids');
                const allInput = qs(document, '#admin-bulk-delete-all');
                const submit = qs(document, '#admin-bulk-delete-submit');
                const sourceForm = qs(root, 'form[data-bulk-form]');
                if (!modal || !form || !sourceForm) return;
                form.action = sourceForm.action;
                idsWrap.innerHTML = '';
                if (state.allCategory) {
                    allInput.value = '1';
                    const total = root.dataset.totalCount || '0';
                    if (countEl) countEl.textContent = total;
                    if (summaryEl) summaryEl.textContent = 'Seluruh ' + total + ' data pada kategori "' + (root.dataset.categoryLabel || '') + '" akan dihapus permanen.';
                    if (namesEl) namesEl.innerHTML = '<li>Semua data pada kategori ini (termasuk halaman lain).</li>';
                } else {
                    allInput.value = '0';
                    state.ids.forEach((id) => { const i = document.createElement('input'); i.type = 'hidden'; i.name = 'ids[]'; i.value = id; idsWrap.appendChild(i); });
                    if (countEl) countEl.textContent = state.ids.length;
                    if (summaryEl) summaryEl.textContent = state.ids.length + ' data terpilih akan dihapus permanen.';
                    if (namesEl) namesEl.innerHTML = state.names.slice(0, 12).map((n) => '<li>' + n.replace(/</g, '&lt;') + '</li>').join('') + (state.names.length > 12 ? '<li>…dan ' + (state.names.length - 12) + ' data lainnya.</li>' : '');
                }
                if (submit) submit.disabled = false;
                openModal(modal);
            }

            qsa(document, '[data-empty-open]').forEach((btn) => btn.addEventListener('click', () => {
                const modal = qs(document, '#admin-empty-modal');
                const form = qs(document, '#admin-empty-form');
                const label = btn.dataset.categoryLabel || '';
                const total = btn.dataset.totalCount || '0';
                const expected = btn.dataset.confirmWord || label.toUpperCase();
                if (!modal || !form) return;
                form.action = btn.dataset.action || '';
                const nameEl = qs(document, '#admin-empty-category'); if (nameEl) nameEl.textContent = label;
                const totalEl = qs(document, '#admin-empty-total'); if (totalEl) totalEl.textContent = total;
                const wordEl = qs(document, '#admin-empty-word'); if (wordEl) wordEl.textContent = expected;
                const input = qs(document, '#admin-empty-confirm'); if (input) { input.value = ''; input.dataset.expected = expected; }
                const submit = qs(document, '#admin-empty-submit'); if (submit) submit.disabled = true;
                openModal(modal);
            }));

            const emptyInput = qs(document, '#admin-empty-confirm');
            if (emptyInput) emptyInput.addEventListener('input', () => {
                const submit = qs(document, '#admin-empty-submit');
                if (submit) submit.disabled = emptyInput.value.trim().toUpperCase() !== (emptyInput.dataset.expected || '');
            });
        })();

        document.querySelectorAll('[data-image-preview]').forEach((input) => {
            input.addEventListener('change', () => {
                const preview = document.querySelector(input.dataset.imagePreview);
                const file = input.files?.[0];

                if (preview && file) {
                    preview.src = URL.createObjectURL(file);
                    preview.hidden = false;
                }
            });
        });
    </script>
    <div id="admin-bulk-delete-modal" class="admin-modal" hidden>
        <div class="admin-modal-card" role="dialog" aria-modal="true" aria-labelledby="admin-bulk-delete-title">
            <h2 id="admin-bulk-delete-title">Hapus Data Terpilih</h2>
            <p id="admin-bulk-delete-summary" class="admin-modal-text"></p>
            <p class="admin-modal-warning">Penghapusan permanen. Data akan dihapus dari database dan tidak dapat dibatalkan. File gambar terkait yang tidak digunakan lagi akan dibersihkan.</p>
            <ul id="admin-bulk-delete-names" class="admin-modal-list"></ul>
            <form id="admin-bulk-delete-form" method="POST">
                @csrf
                <input type="hidden" name="select_all" id="admin-bulk-delete-all" value="0">
                <div id="admin-bulk-delete-ids"></div>
                <div class="admin-modal-actions">
                    <button type="button" class="admin-button admin-button-secondary" data-modal-close>Batal</button>
                    <button type="submit" id="admin-bulk-delete-submit" class="admin-button admin-button-danger">Hapus Permanen (<span id="admin-bulk-delete-count">0</span>)</button>
                </div>
            </form>
        </div>
    </div>

    <div id="admin-empty-modal" class="admin-modal" hidden>
        <div class="admin-modal-card" role="dialog" aria-modal="true" aria-labelledby="admin-empty-title">
            <h2 id="admin-empty-title">Kosongkan Semua Data</h2>
            <p class="admin-modal-text">Anda akan menghapus <strong>seluruh data <span id="admin-empty-category"></span></strong> sebanyak <strong><span id="admin-empty-total">0</span> data</strong>. Hanya kategori yang sedang dibuka yang dikosongkan.</p>
            <p class="admin-modal-warning">Tindakan ini tidak dapat dibatalkan. Seluruh record kategori ini akan dihapus permanen dari database (file gambar terkait yang tidak digunakan akan dibersihkan). Activity Log tidak dihapus.</p>
            <form id="admin-empty-form" method="POST">
                @csrf
                <label class="admin-field" for="admin-empty-confirm">Ketik <strong id="admin-empty-word"></strong> untuk melanjutkan</label>
                <input id="admin-empty-confirm" name="confirm_name" type="text" autocomplete="off" class="admin-modal-input" placeholder="Nama kategori">
                <div class="admin-modal-actions">
                    <button type="button" class="admin-button admin-button-secondary" data-modal-close>Batal</button>
                    <button type="submit" id="admin-empty-submit" class="admin-button admin-button-danger" disabled>Hapus Permanen Semua</button>
                </div>
            </form>
        </div>
    </div>

    <dialog class="admin-media-editor" data-image-editor>
        <form method="dialog" class="admin-media-editor-card">
            <div class="admin-card-heading">
                <div><h2>Edit foto</h2><p>Geser foto, atur crop dan penyesuaian ringan sebelum menyimpan.</p></div>
                <button type="submit" class="admin-icon-button" aria-label="Tutup editor">×</button>
            </div>
            <canvas width="720" height="480" data-image-editor-canvas></canvas>
            <div class="admin-media-editor-controls">
                <label>Rasio
                    <select data-image-ratio>
                        <option value="original">Original</option>
                        <option value="1:1">1:1</option>
                        <option value="4:3">4:3</option>
                        <option value="3:4">3:4</option>
                        <option value="16:9">16:9</option>
                        <option value="9:16">9:16</option>
                    </select>
                </label>
                <label>Zoom <input type="range" min="1" max="3" step="0.05" value="1" data-image-zoom></label>
                <label>Brightness <input type="range" min="70" max="130" value="100" data-image-brightness></label>
                <label>Contrast <input type="range" min="70" max="130" value="100" data-image-contrast></label>
                <label>Saturation <input type="range" min="70" max="130" value="100" data-image-saturation></label>
            </div>
            <div class="admin-media-editor-actions">
                <button type="button" class="admin-button admin-button-secondary" data-image-rotate>Putar 90°</button>
                <button type="button" class="admin-button admin-button-secondary" data-image-flip-horizontal>Flip horizontal</button>
                <button type="button" class="admin-button admin-button-secondary" data-image-flip-vertical>Flip vertical</button>
                <button type="button" class="admin-button admin-button-secondary" data-image-reset>Reset foto</button>
                <button type="submit" class="admin-button admin-button-secondary" data-image-cancel>Batal</button>
                <button type="button" class="admin-button admin-button-primary" data-image-apply>Simpan edit</button>
            </div>
        </form>
    </dialog>
</body>
</html>