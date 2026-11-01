import { createIcons, Menu, ChevronDown, ArrowUpRight, ArrowRight, GraduationCap, BookOpen, Users, UserCheck, CalendarDays, Newspaper, MapPin, Phone, Mail } from 'lucide';

import AOS from 'aos';
import 'aos/dist/aos.css';

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

    const setTeacherCardOpen = (activeCard, isOpen) => {
        teacherCards.forEach((card) => {
            const cardIsOpen = isOpen && card === activeCard;
            const trigger = card.querySelector('.teacher-card-trigger');
            const details = card.querySelector('.teacher-card-details');

            card.classList.toggle('is-open', cardIsOpen);
            trigger.setAttribute('aria-expanded', String(cardIsOpen));
            details.setAttribute('aria-hidden', String(!cardIsOpen));
            details.inert = !cardIsOpen;
        });
    };

    teacherCards.forEach((card) => {
        const trigger = card.querySelector('.teacher-card-trigger');
        const closeButton = card.querySelector('.teacher-card-close');
        const isPrincipalCard = card.classList.contains('principal-card');
        const hoverTarget = isPrincipalCard
            ? card.querySelector('.principal-portrait-media')
            : card;
        let skipNextFocusOpen = false;

        hoverTarget.addEventListener('pointerenter', (event) => {
            if (event.pointerType === 'mouse') {
                setTeacherCardOpen(card, true);
            }
        });

        const closeOnPointerLeave = (event) => {
            if (event.pointerType === 'mouse') {
                setTeacherCardOpen(card, false);
            }
        };

        card.addEventListener('pointerleave', closeOnPointerLeave);

        if (isPrincipalCard) {
            hoverTarget.addEventListener('pointerleave', closeOnPointerLeave);
        }

        card.addEventListener('focusin', (event) => {
            if (!skipNextFocusOpen && (!isPrincipalCard || event.target === trigger)) {
                setTeacherCardOpen(card, true);
            }
        });

        card.addEventListener('focusout', (event) => {
            if (isPrincipalCard ? event.relatedTarget !== trigger : !card.contains(event.relatedTarget)) {
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
            if (event.detail === 0 || event.pointerType === 'touch') {
                setTeacherCardOpen(card, trigger.getAttribute('aria-expanded') !== 'true');
            } else {
                setTeacherCardOpen(card, true);
            }
        });

        card.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setTeacherCardOpen(card, false);
                skipNextFocusOpen = true;
                trigger.focus();
                skipNextFocusOpen = false;
            }
        });
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

    // Buka dan tutup hamburger
    menuToggle.addEventListener('click', () => {
        const isOpen = navMenu.classList.toggle('open');

        menuToggle.classList.toggle('active', isOpen);
        menuToggle.setAttribute('aria-expanded', String(isOpen));
    });

    // Dropdown melalui klik
    dropdowns.forEach((dropdown) => {
        const toggle = dropdown.querySelector('.dropdown-toggle');

        toggle.addEventListener('click', () => {
            const willOpen = !dropdown.classList.contains('open');

            dropdowns.forEach((item) => {
                item.classList.remove('open');
                item.querySelector('.dropdown-toggle')
                    .setAttribute('aria-expanded', 'false');
            });

            if (willOpen) {
                dropdown.classList.add('open');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // Tutup ketika klik di luar navigasi
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.nav-dropdown')) {
            dropdowns.forEach((dropdown) => {
                dropdown.classList.remove('open');
                dropdown.querySelector('.dropdown-toggle')
                    .setAttribute('aria-expanded', 'false');
            });
        }

        if (
            !event.target.closest('.nav-wrapper') &&
            navMenu.classList.contains('open')
        ) {
            navMenu.classList.remove('open');
            menuToggle.classList.remove('active');
            menuToggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Tutup dengan tombol Escape
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            dropdowns.forEach((dropdown) => {
                dropdown.classList.remove('open');
                dropdown.querySelector('.dropdown-toggle')
                    .setAttribute('aria-expanded', 'false');
            });

            navMenu.classList.remove('open');
            menuToggle.classList.remove('active');
            menuToggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Tutup menu setelah memilih halaman
    navMenu.querySelectorAll('.dropdown-menu a, a.nav-link').forEach((link) => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('open');
            menuToggle.classList.remove('active');
            menuToggle.setAttribute('aria-expanded', 'false');
        });
    });
});
