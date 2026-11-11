import { createIcons, Menu, ChevronDown, ArrowUpRight, ArrowRight, GraduationCap, BookOpen, Users, UserCheck, CalendarDays, Newspaper, MapPin, Phone, Mail } from 'lucide';

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

    const teacherCards = [...document.querySelectorAll('.teacher-card')];

    const syncTeacherScrollLock = () => {
        const openModal = teacherCards.some((card) => card.classList.contains('is-open') && card.classList.contains('educator-card'));
        document.body.classList.toggle('teacher-modal-open', openModal);
    };

    const setTeacherCardOpen = (activeCard, isOpen) => {
        teacherCards.forEach((card) => {
            const cardIsOpen = isOpen && card === activeCard;
            const trigger = card.querySelector('.teacher-card-trigger');
            const details = card.querySelector('.teacher-card-details');

            if (!trigger || !details) return;

            card.classList.toggle('is-open', cardIsOpen);
            trigger.setAttribute('aria-expanded', String(cardIsOpen));
            details.setAttribute('aria-hidden', String(!cardIsOpen));
            details.inert = !cardIsOpen;
        });
        syncTeacherScrollLock();
    };

    teacherCards.forEach((card) => {
        const trigger = card.querySelector('.teacher-card-trigger');
        const closeButton = card.querySelector('.teacher-card-close');
        const isPrincipalCard = card.classList.contains('principal-card');
        const isEducatorCard = card.classList.contains('educator-card');
        const hoverTarget = isPrincipalCard
            ? card.querySelector('.principal-portrait-frame')
            : card;
        let skipNextFocusOpen = false;

        const closeOnPointerLeave = (event) => {
            if (event.pointerType === 'mouse') {
                setTeacherCardOpen(card, false);
            }
        };

        if (!isEducatorCard) {
            hoverTarget.addEventListener('pointerenter', (event) => {
                if (event.pointerType === 'mouse') {
                    setTeacherCardOpen(card, true);
                }
            });

            card.addEventListener('pointerleave', closeOnPointerLeave);

            if (isPrincipalCard) {
                hoverTarget.addEventListener('pointerleave', closeOnPointerLeave);
            }
        }

        card.addEventListener('focusin', (event) => {
            if (!isEducatorCard && !skipNextFocusOpen && (!isPrincipalCard || event.target === trigger)) {
                setTeacherCardOpen(card, true);
            }
        });

        card.addEventListener('focusout', (event) => {
            if (!isEducatorCard && (isPrincipalCard ? event.relatedTarget !== trigger : !card.contains(event.relatedTarget))) {
                setTeacherCardOpen(card, false);
            }
        });

        if (closeButton) {
            closeButton.addEventListener('click', () => {
                setTeacherCardOpen(card, false);
                skipNextFocusOpen = true;
                trigger.focus();
                skipNextFocusOpen = false;
            });
        }

        trigger.addEventListener('click', (event) => {
            if (isEducatorCard) {
                event.preventDefault();
                const willOpen = trigger.getAttribute('aria-expanded') !== 'true';

                setTeacherCardOpen(card, willOpen);

                if (willOpen) {
                    window.setTimeout(() => closeButton?.focus(), 0);
                } else {
                    trigger.focus();
                }

                return;
            }
            if (event.detail === 0 || event.pointerType === 'touch') {
                setTeacherCardOpen(card, trigger.getAttribute('aria-expanded') !== 'true');
            } else {
                setTeacherCardOpen(card, true);
            }
        });

        if (isEducatorCard) {
            document.addEventListener('click', (event) => {
                if (!(event.target instanceof Node) || !card.classList.contains('is-open')) return;
                const panel = card.querySelector('.teacher-card-details');
                if (!panel) return;
                if (!panel.contains(event.target) && !trigger.contains(event.target)) {
                    setTeacherCardOpen(card, false);
                    trigger.focus();
                }
            });
        }

        card.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setTeacherCardOpen(card, false);
                skipNextFocusOpen = true;
                trigger.focus();
                skipNextFocusOpen = false;
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        const openEducatorCard = teacherCards.find((card) => (
            card.classList.contains('educator-card') && card.classList.contains('is-open')
        ));

        if (openEducatorCard) {
            setTeacherCardOpen(openEducatorCard, false);
            openEducatorCard.querySelector('.teacher-card-trigger')?.focus();
        }
    });

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
            dropdown.classList.remove('open');
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

    // Dropdown melalui klik (khusus mobile atau saat toggle dropdown diklik)
    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (!toggle) return;

        toggle.addEventListener('click', (event) => {
            const isMobile = window.innerWidth <= 900;
            const href = toggle.getAttribute('href') || '';
            const isAnchorOnly = href === '#' || href === '' || href.startsWith('/#');

            if (isMobile || isAnchorOnly) {
                event.preventDefault();
            }

            const willOpen = !dropdown.classList.contains('open');

            dropdowns.forEach((item) => {
                if (item !== dropdown) {
                    item.classList.remove('open');
                    item.querySelector('.dropdown-toggle')
                        ?.setAttribute('aria-expanded', 'false');
                }
            });

            dropdown.classList.toggle('open', willOpen);
            toggle.setAttribute('aria-expanded', String(willOpen));
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

    // Tutup mobile menu saat resize ke layar desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 900 && navMenu.classList.contains('open')) {
            closeMobileMenu();
        }
    });
});
