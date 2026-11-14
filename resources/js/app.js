import { createIcons, Menu, ChevronDown, ArrowUpRight, ArrowRight, GraduationCap, BookOpen, Users, UserCheck, CalendarDays, Newspaper, MapPin, Phone, Mail, ShieldCheck, UserRound, AtSign, LockKeyhole, Eye, EyeOff, KeyRound, CircleAlert, Info, ArrowLeft, Check, LayoutDashboard, BriefcaseBusiness, Images, HardDrive, Settings2, UsersRound, CircleUserRound, ExternalLink, PanelLeftClose, PanelLeftOpen, LogOut, Activity } from 'lucide';

import AOS from 'aos';
import 'aos/dist/aos.css';
import './media-manager.js';

document.addEventListener('DOMContentLoaded', () => {
        createIcons({
        icons: {
            Menu,
            ChevronDown,
            ArrowUpRight,
            ArrowRight,
            GraduationCap,
            BookOpen,
            Users,
            UserCheck,
            CalendarDays,
            Newspaper,
            MapPin,
            Phone,
            Mail,
        },
    });

    AOS.init({
        duration: 650,
        once: true,
        offset: 70,
        easing: 'ease-out-cubic',
    });

    createIcons({
        icons: {
            ShieldCheck,
            UserRound,
            AtSign,
            LockKeyhole,
            Eye,
            EyeOff,
            KeyRound,
            CircleAlert,
            Info,
            ArrowLeft,
            Check,
            LayoutDashboard,
            BriefcaseBusiness,
            Images,
            HardDrive,
            Settings2,
            UsersRound,
            CircleUserRound,
            ExternalLink,
            PanelLeftClose,
            PanelLeftOpen,
            LogOut,
            Activity,
        },
    });

    const sidebarCollapseToggle = document.querySelector('[data-admin-sidebar-toggle]');

    if (sidebarCollapseToggle) {
        const collapseIcon = document.querySelector('[data-sidebar-collapse-icon]');
        const expandIcon = document.querySelector('[data-sidebar-expand-icon]');

        sidebarCollapseToggle.addEventListener('click', () => {
            const isCollapsed = document.body.classList.toggle('admin-sidebar-collapsed');

            if (collapseIcon) {
                collapseIcon.hidden = isCollapsed;
            }

            if (expandIcon) {
                expandIcon.hidden = !isCollapsed;
            }

            sidebarCollapseToggle.setAttribute('aria-expanded', String(!isCollapsed));
            sidebarCollapseToggle.setAttribute('aria-label', isCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar');
            sidebarCollapseToggle.setAttribute('title', isCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar');
        });
    }

    const mobileDrawerToggle = document.getElementById('admin-drawer-toggle');

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobileDrawerToggle?.checked) {
            mobileDrawerToggle.checked = false;
        }
    });

    document.querySelectorAll('[data-admin-profile-dropdown]').forEach((dropdown) => {
        document.addEventListener('click', (event) => {
            if (!(event.target instanceof Node) || !dropdown.contains(event.target)) {
                dropdown.open = false;
            }
        });

        dropdown.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                dropdown.open = false;
                dropdown.querySelector('summary')?.focus();
            }
        });
    });

    document.querySelectorAll('[data-setup-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.getAttribute('aria-controls'));

            if (!(input instanceof HTMLInputElement)) {
                return;
            }

            const shouldShowPassword = input.type === 'password';
            input.type = shouldShowPassword ? 'text' : 'password';
            button.setAttribute('aria-label', shouldShowPassword ? 'Sembunyikan password' : 'Tampilkan password');
            button.querySelector('[data-password-eye]').hidden = shouldShowPassword;
            button.querySelector('[data-password-eye-off]').hidden = !shouldShowPassword;
        });
    });

    const setupPassword = document.querySelector('[data-setup-password]');
    const passwordMeter = document.querySelector('[data-password-meter]');
    const passwordMeterFill = document.querySelector('[data-password-meter-fill]');
    const passwordStrengthLabel = document.querySelector('[data-password-strength-label]');

    if (setupPassword && passwordMeter && passwordMeterFill && passwordStrengthLabel) {
        setupPassword.addEventListener('input', () => {
            const password = setupPassword.value;
            const checks = [
                password.length >= 8,
                /[a-z]/.test(password),
                /[A-Z]/.test(password),
                /\d/.test(password),
                /[^a-zA-Z0-9]/.test(password),
            ];
            const score = checks.filter(Boolean).length;
            const strength = password.length === 0
                ? 'empty'
                : score <= 2
                    ? 'weak'
                    : score <= 4
                        ? 'medium'
                        : 'strong';
            const labels = {
                empty: 'Belum diisi',
                weak: 'Lemah',
                medium: 'Sedang',
                strong: 'Kuat',
            };

            passwordMeter.dataset.strength = strength;
            passwordMeter.setAttribute('aria-valuenow', String(score));
            passwordMeterFill.style.width = `${score * 20}%`;
            passwordStrengthLabel.textContent = labels[strength];
        });
    }

    const setupForm = document.querySelector('[data-setup-form]');

    if (setupForm) {
        setupForm.addEventListener('submit', () => {
            if (!setupForm.checkValidity()) {
                return;
            }

            const submitButton = setupForm.querySelector('[data-setup-submit]');

            if (!submitButton) {
                return;
            }

            submitButton.disabled = true;
            submitButton.setAttribute('aria-busy', 'true');
            submitButton.querySelector('[data-submit-label]').textContent = 'Membuat akun...';
            submitButton.querySelector('[data-submit-arrow]').hidden = true;
            submitButton.querySelector('[data-submit-spinner]').hidden = false;
        });
    }

    document.querySelectorAll('[data-copy-article-link]').forEach((button) => {
        const status = document.querySelector('[data-copy-article-status]');

        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copyArticleLink);
                status.textContent = 'Tautan berhasil disalin.';
            } catch (error) {
                status.textContent = 'Tautan tidak dapat disalin. Salin alamat halaman dari browser.';
                console.error('Gagal menyalin tautan artikel.', error);
            }
        });
    });

    const programAccordion = document.querySelector('[data-program-accordion]');

    if (programAccordion) {
        const programCards = [...programAccordion.querySelectorAll('.program-card')];
        const defaultProgramCard = programCards[0];

        const activateProgramCard = (activeCard) => {
            programCards.forEach((card) => {
                const isActive = card === activeCard;
                const trigger = card.querySelector('.program-card-trigger');
                const content = card.querySelector('.program-card-content');

                card.classList.toggle('is-active', isActive);
                card.dataset.active = String(isActive);
                trigger.setAttribute('aria-expanded', String(isActive));
                content.hidden = !isActive;
            });
        };

        programAccordion.addEventListener('pointerover', (event) => {
            if (event.pointerType !== 'mouse' || !(event.target instanceof Element)) {
                return;
            }

            const card = event.target.closest('.program-card');

            if (card && programAccordion.contains(card)) {
                activateProgramCard(card);
            }
        });

        programAccordion.addEventListener('pointerleave', (event) => {
            if (
                event.pointerType === 'mouse' &&
                window.matchMedia('(hover: hover) and (pointer: fine)').matches
            ) {
                activateProgramCard(defaultProgramCard);
            }
        });

        programAccordion.addEventListener('click', (event) => {
            if (!(event.target instanceof Element)) {
                return;
            }

            const trigger = event.target.closest('.program-card-trigger');
            const card = trigger?.closest('.program-card');

            if (card && programAccordion.contains(card)) {
                activateProgramCard(card);
            }
        });

        programAccordion.addEventListener('focusin', (event) => {
            if (!(event.target instanceof Element)) {
                return;
            }

            const card = event.target.closest('.program-card');

            if (card) {
                activateProgramCard(card);
            }
        });
    }

    const educatorCards = [...document.querySelectorAll('.teacher-card.educator-card')];
    const focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
    const educatorCloseFallbacks = new WeakMap();

    const updateEducatorModalState = () => {
        document.body.classList.toggle(
            'teacher-modal-open',
            educatorCards.some((card) => (
                card.classList.contains('is-open') || card.classList.contains('is-closing')
            )),
        );
    };

    const clearEducatorCloseFallback = (card) => {
        const timer = educatorCloseFallbacks.get(card);

        if (timer !== undefined) {
            window.clearTimeout(timer);
            educatorCloseFallbacks.delete(card);
        }
    };

    const finishEducatorDialogClose = (card) => {
        if (!card.classList.contains('is-closing')) {
            return;
        }

        clearEducatorCloseFallback(card);
        card.classList.remove('is-closing');
        updateEducatorModalState();
    };

    const setEducatorDialogOpen = (activeCard, isOpen) => {
        educatorCards.forEach((card) => {
            const isActive = isOpen && card === activeCard;
            const isClosing = !isOpen
                && card === activeCard
                && card.classList.contains('is-open');
            const trigger = card.querySelector('.teacher-card-trigger');
            const dialog = card.querySelector('.teacher-card-details');

            if (!trigger || !dialog) {
                return;
            }

            clearEducatorCloseFallback(card);
            card.classList.toggle('is-closing', isClosing);
            card.classList.toggle('is-open', isActive);
            trigger.setAttribute('aria-expanded', String(isActive));
            dialog.setAttribute('aria-hidden', String(!isActive));
            dialog.inert = !isActive;
        });

        updateEducatorModalState();

        if (!isOpen && activeCard.classList.contains('is-closing')) {
            const timer = window.setTimeout(() => finishEducatorDialogClose(activeCard), 350);
            educatorCloseFallbacks.set(activeCard, timer);
        }
    };

    document.addEventListener('transitionend', (event) => {
        if (!(event.target instanceof Element)
            || !event.target.matches('.staff-profile-dialog')
            || event.propertyName !== 'opacity') {
            return;
        }

        const card = event.target.closest('.teacher-card.educator-card.is-closing');

        if (card) {
            finishEducatorDialogClose(card);
        }
    });

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const control = event.target.closest('.teacher-card-trigger, .teacher-card-close, .staff-id-backdrop');
        const card = control?.closest('.teacher-card.educator-card');

        if (!control || !card) {
            return;
        }

        if (control.matches('.teacher-card-trigger')) {
            const isOpen = control.getAttribute('aria-expanded') === 'true';
            setEducatorDialogOpen(card, !isOpen);

            if (isOpen) {
                control.focus();
            } else {
                card.querySelector('.teacher-card-close')?.focus();
            }

            return;
        }

        setEducatorDialogOpen(card, false);
        card.querySelector('.teacher-card-trigger')?.focus();
    });

    document.addEventListener('keydown', (event) => {
        const openCard = educatorCards.find((card) => card.classList.contains('is-open'));

        if (!openCard) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            setEducatorDialogOpen(openCard, false);
            openCard.querySelector('.teacher-card-trigger')?.focus();

            return;
        }

        if (event.key !== 'Tab') {
            return;
        }

        const dialog = openCard.querySelector('.teacher-card-details');
        const focusableElements = dialog
            ? [...dialog.querySelectorAll(focusableSelector)].filter((element) => element.getClientRects().length > 0)
            : [];

        if (focusableElements.length === 0) {
            return;
        }

        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        const focusIsOutsideDialog = !dialog.contains(document.activeElement);

        if (focusIsOutsideDialog) {
            event.preventDefault();
            (event.shiftKey ? lastElement : firstElement).focus();
        } else if (event.shiftKey && document.activeElement === firstElement) {
            event.preventDefault();
            lastElement.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
            event.preventDefault();
            firstElement.focus();
        }
    });

    const featuredNewsRotator = document.querySelector('[data-news-featured-rotator]');

    if (featuredNewsRotator) {
        const featuredSlides = [...featuredNewsRotator.querySelectorAll('[data-news-featured-slide]')];
        const featuredControls = [
            ...featuredNewsRotator.querySelectorAll('[data-news-featured-direction]'),
        ];

        if (featuredSlides.length > 1) {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
            let activeSlideIndex = featuredSlides.findIndex((slide) => slide.classList.contains('is-active'));
            let rotationTimer;
            let pointerIsOver = false;
            let focusIsWithin = false;

            const clearRotation = () => {
                if (rotationTimer !== undefined) {
                    window.clearInterval(rotationTimer);
                    rotationTimer = undefined;
                }
            };

            const activateSlide = (slideIndex) => {
                activeSlideIndex = (slideIndex + featuredSlides.length) % featuredSlides.length;

                featuredSlides.forEach((slide, index) => {
                    const isActive = index === activeSlideIndex;

                    slide.classList.toggle('is-active', isActive);
                    slide.setAttribute('aria-hidden', String(!isActive));
                });
            };

            const startRotation = () => {
                clearRotation();

                if (prefersReducedMotion.matches || document.hidden || pointerIsOver || focusIsWithin) {
                    return;
                }

                rotationTimer = window.setInterval(() => {
                    activateSlide(activeSlideIndex + 1);
                }, 5000);
            };

            featuredControls.forEach((button) => {
                button.addEventListener('click', () => {
                    const direction = button.dataset.newsFeaturedDirection === 'previous' ? -1 : 1;

                    activateSlide(activeSlideIndex + direction);
                    startRotation();
                });
            });

            featuredNewsRotator.addEventListener('pointerenter', (event) => {
                if (event.pointerType === 'mouse') {
                    pointerIsOver = true;
                    clearRotation();
                }
            });

            featuredNewsRotator.addEventListener('pointerleave', (event) => {
                if (event.pointerType === 'mouse') {
                    pointerIsOver = false;
                    startRotation();
                }
            });

            featuredNewsRotator.addEventListener('focusin', () => {
                focusIsWithin = true;
                clearRotation();
            });

            featuredNewsRotator.addEventListener('focusout', (event) => {
                if (!featuredNewsRotator.contains(event.relatedTarget)) {
                    focusIsWithin = false;
                    startRotation();
                }
            });

            document.addEventListener('visibilitychange', startRotation);
            startRotation();
        }
    }

    const newsCarousel = document.querySelector('[data-news-track]');

    if (newsCarousel) {
        const newsCarouselWrapper = newsCarousel.closest('.news-carousel');
        const newsScrollButtons = [
            ...newsCarouselWrapper.querySelectorAll('[data-news-scroll]'),
        ];
        const newsPages = newsCarouselWrapper.querySelector('[data-news-pages]');
        let newsPageButtons = [];
        let scrollUpdateScheduled = false;

        const getNewsPageScrollLeft = (pageIndex) => {
            const maxScrollLeft = newsCarousel.scrollWidth - newsCarousel.clientWidth;

            return newsPageButtons.length <= 1
                ? 0
                : (maxScrollLeft * pageIndex) / (newsPageButtons.length - 1);
        };

        const renderNewsPageButtons = () => {
            const maxScrollLeft = newsCarousel.scrollWidth - newsCarousel.clientWidth;
            const pageCount = Math.max(
                1,
                Math.ceil(maxScrollLeft / newsCarousel.clientWidth) + 1,
            );

            newsPages.replaceChildren();
            newsPageButtons = [];
            newsPages.hidden = pageCount <= 1;

            for (let pageIndex = 0; pageIndex < pageCount; pageIndex += 1) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'news-carousel-page';
                button.setAttribute('aria-label', `Tampilkan halaman berita ${pageIndex + 1}`);
                button.addEventListener('click', () => {
                    newsCarousel.scrollTo({
                        left: getNewsPageScrollLeft(pageIndex),
                        behavior: 'smooth',
                    });
                });

                newsPages.append(button);
                newsPageButtons.push(button);
            }
        };

        const updateNewsScrollButtons = () => {
            const maxScrollLeft = newsCarousel.scrollWidth - newsCarousel.clientWidth;

            newsScrollButtons.forEach((button) => {
                button.disabled = button.dataset.newsScroll === 'left'
                    ? newsCarousel.scrollLeft <= 1
                    : newsCarousel.scrollLeft >= maxScrollLeft - 1;
            });

            const activePage = newsPageButtons.reduce((nearestPage, button, pageIndex) => {
                const currentDistance = Math.abs(
                    newsCarousel.scrollLeft - getNewsPageScrollLeft(pageIndex),
                );
                const nearestDistance = Math.abs(
                    newsCarousel.scrollLeft - getNewsPageScrollLeft(nearestPage),
                );

                return currentDistance < nearestDistance ? pageIndex : nearestPage;
            }, 0);

            newsPageButtons.forEach((button, pageIndex) => {
                if (pageIndex === activePage) {
                    button.setAttribute('aria-current', 'true');
                } else {
                    button.removeAttribute('aria-current');
                }
            });
        };

        newsScrollButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const direction = button.dataset.newsScroll === 'left' ? -1 : 1;

                newsCarousel.scrollBy({
                    left: direction * newsCarousel.clientWidth * 0.8,
                    behavior: 'smooth',
                });
            });
        });

        newsCarousel.addEventListener('scroll', () => {
            if (scrollUpdateScheduled) {
                return;
            }

            scrollUpdateScheduled = true;
            window.requestAnimationFrame(() => {
                updateNewsScrollButtons();
                scrollUpdateScheduled = false;
            });
        });

        window.addEventListener('resize', () => {
            renderNewsPageButtons();
            updateNewsScrollButtons();
        });
        renderNewsPageButtons();
        updateNewsScrollButtons();
    }

    const videoCarousel = document.querySelector('[data-video-track]');

    if (videoCarousel) {
        const videoScrollButtons = [
            ...videoCarousel.parentElement.querySelectorAll('[data-video-scroll]'),
        ];

        const updateVideoScrollButtons = () => {
            const maxScrollLeft = videoCarousel.scrollWidth - videoCarousel.clientWidth;

            videoScrollButtons.forEach((button) => {
                button.disabled = button.dataset.videoScroll === 'left'
                    ? videoCarousel.scrollLeft <= 1
                    : videoCarousel.scrollLeft >= maxScrollLeft - 1;
            });
        };

        videoScrollButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const direction = button.dataset.videoScroll === 'left' ? -1 : 1;

                videoCarousel.scrollBy({
                    left: direction * videoCarousel.clientWidth,
                    behavior: 'smooth',
                });
            });
        });

        videoCarousel.addEventListener('scroll', updateVideoScrollButtons);
        window.addEventListener('resize', updateVideoScrollButtons);
        updateVideoScrollButtons();
    }

    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');
    const dropdowns = document.querySelectorAll('.nav-dropdown');

    if (!menuToggle || !navMenu) return;

    const closeAllDropdowns = () => {
        dropdowns.forEach((dropdown) => {
            dropdown.classList.remove('open', 'hover-open');
            dropdown.querySelector('.dropdown-toggle')
                ?.setAttribute('aria-expanded', 'false');
        });
    };

    const closeMobileMenu = () => {
        navMenu.classList.remove('open');
        menuToggle.classList.remove('active');
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
        closeAllDropdowns();
    };

    // Buka dan tutup hamburger
    menuToggle.addEventListener('click', () => {
        const isOpen = navMenu.classList.toggle('open');

        menuToggle.classList.toggle('active', isOpen);
        menuToggle.setAttribute('aria-expanded', String(isOpen));
        document.body.classList.toggle('menu-open', isOpen);

        if (!isOpen) {
            closeAllDropdowns();
        }
    });

    // Hover membuka dropdown di desktop; klik mempertahankan status toggle.
    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (!toggle) return;

        dropdown.addEventListener('pointerenter', (event) => {
            if (
                event.pointerType !== 'mouse' ||
                window.innerWidth <= 1100 ||
                !window.matchMedia('(hover: hover) and (pointer: fine)').matches
            ) {
                return;
            }

            dropdowns.forEach((item) => {
                if (item !== dropdown) {
                    item.classList.remove('open', 'hover-open');
                    item.querySelector('.dropdown-toggle')
                        ?.setAttribute('aria-expanded', 'false');
                }
            });

            dropdown.classList.add('hover-open');
            toggle.setAttribute('aria-expanded', 'true');
        });

        dropdown.addEventListener('pointerleave', (event) => {
            if (event.pointerType !== 'mouse') {
                return;
            }

            dropdown.classList.remove('hover-open');
            toggle.setAttribute('aria-expanded', String(dropdown.classList.contains('open')));
        });

        toggle.addEventListener('click', (event) => {
            event.preventDefault();

            const willOpen = !dropdown.classList.contains('open');

            dropdowns.forEach((item) => {
                if (item !== dropdown) {
                    item.classList.remove('open', 'hover-open');
                    item.querySelector('.dropdown-toggle')
                        ?.setAttribute('aria-expanded', 'false');
                }
            });

            dropdown.classList.toggle('open', willOpen);
            toggle.setAttribute(
                'aria-expanded',
                String(willOpen || dropdown.classList.contains('hover-open')),
            );
        });

        toggle.addEventListener('keydown', (event) => {
            if (event.key === ' ') {
                event.preventDefault();
                toggle.click();
            }
        });
    });

    // Tutup ketika klik di luar navigasi
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.nav-dropdown')) {
            closeAllDropdowns();
        }

        if (
            !event.target.closest('.nav-wrapper') &&
            navMenu.classList.contains('open')
        ) {
            closeMobileMenu();
        }
    });

    // Tutup dengan tombol Escape
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMobileMenu();
        }
    });

    // Tutup menu setelah memilih halaman tautan (bukan dropdown toggle)
    navMenu.querySelectorAll('.dropdown-menu a, a.nav-link:not(.dropdown-toggle)').forEach((link) => {
        link.addEventListener('click', () => {
            closeMobileMenu();
        });
    });

    // Reset hover di layar kecil dan tutup menu seluler saat kembali ke desktop.
    window.addEventListener('resize', () => {
        if (window.innerWidth <= 1100) {
            dropdowns.forEach((dropdown) => {
                dropdown.classList.remove('hover-open');
                dropdown.querySelector('.dropdown-toggle')
                    ?.setAttribute('aria-expanded', String(dropdown.classList.contains('open')));
            });
        }

        if (window.innerWidth > 900 && navMenu.classList.contains('open')) {
            closeMobileMenu();
        }
    });
});
