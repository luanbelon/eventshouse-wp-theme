/**
 * EventsHouse Theme - Main JavaScript
 * 
 * @package EventsHouse
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    // ════════════════════════════════════════
    // HEADER - Scroll Effect & Mobile Menu
    // ════════════════════════════════════════
    const header = document.getElementById('site-header');
    const mobileToggle = document.getElementById('mobile-menu-toggle');
    const mobileOverlay = document.getElementById('mobile-nav-overlay');

    // Sticky header scroll effect
    function handleScroll() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }

    window.addEventListener('scroll', handleScroll, { passive: true });

    // Mobile menu toggle
    if (mobileToggle && mobileOverlay) {
        mobileToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileOverlay.classList.toggle('active');
            document.body.style.overflow = mobileOverlay.classList.contains('active') ? 'hidden' : '';
            this.setAttribute('aria-expanded', mobileOverlay.classList.contains('active'));
        });

        // Close mobile menu on link click
        const mobileLinks = mobileOverlay.querySelectorAll('a');
        mobileLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                mobileToggle.classList.remove('active');
                mobileOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    }

    // ════════════════════════════════════════
    // HERO SLIDER (todos os blocos .hero-slider: slide + mobile bubbles)
    // ════════════════════════════════════════
    document.querySelectorAll('.hero-slider').forEach(function(slider) {
        const slides = slider.querySelectorAll('.hero-slide');
        const dots = slider.querySelectorAll('.slider-dot');
        if (!slides.length) {
            return;
        }

        let currentSlide = 0;
        let slideInterval;

        function showSlide(index) {
            slides.forEach(function(slide) { slide.classList.remove('active'); });
            dots.forEach(function(dot) { dot.classList.remove('active'); });

            currentSlide = index;
            if (currentSlide >= slides.length) currentSlide = 0;
            if (currentSlide < 0) currentSlide = slides.length - 1;

            slides[currentSlide].classList.add('active');
            if (dots[currentSlide]) {
                dots[currentSlide].classList.add('active');
            }
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        function startSlider() {
            slideInterval = setInterval(nextSlide, 4000);
        }

        function stopSlider() {
            clearInterval(slideInterval);
        }

        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                stopSlider();
                showSlide(parseInt(this.dataset.slide, 10));
                startSlider();
            });
        });

        startSlider();

        slider.addEventListener('mouseenter', stopSlider);
        slider.addEventListener('mouseleave', startSlider);
    });

    // ════════════════════════════════════════
    // BUBBLES ANIMATION
    // ════════════════════════════════════════
    const bubblesContainer = document.getElementById('herobubblesContainer');

    if (bubblesContainer) {
        const colors = [
            'rgba(255, 200, 221, 0.6)',  // pink
            'rgba(189, 224, 254, 0.6)',  // blue
            'rgba(216, 243, 220, 0.6)',  // mint
            'rgba(255, 243, 176, 0.6)',  // yellow
            'rgba(226, 209, 249, 0.6)',  // lavender
            'rgba(255, 214, 165, 0.6)',  // peach
        ];

        function createBubble() {
            const bubble = document.createElement('div');
            bubble.classList.add('hero-bubble');
            
            const size = Math.random() * 80 + 20;
            const left = Math.random() * 100;
            const color = colors[Math.floor(Math.random() * colors.length)];
            const duration = Math.random() * 8 + 6;
            const delay = Math.random() * 2;

            bubble.style.cssText = [
                'width: ' + size + 'px',
                'height: ' + size + 'px',
                'left: ' + left + '%',
                'bottom: -' + size + 'px',
                'background: ' + color,
                'border: 2px solid rgba(255,255,255,0.3)',
                'animation-duration: ' + duration + 's',
                'animation-delay: ' + delay + 's',
                'opacity: 0'
            ].join(';');

            bubblesContainer.appendChild(bubble);

            // Remove bubble after animation
            setTimeout(function() {
                if (bubble.parentNode) {
                    bubble.parentNode.removeChild(bubble);
                }
            }, (duration + delay) * 1000);
        }

        // Create bubbles periodically
        setInterval(createBubble, 500);
        // Initial batch
        for (var i = 0; i < 8; i++) {
            setTimeout(createBubble, i * 300);
        }
    }

    // ════════════════════════════════════════
    // SCROLL REVEAL ANIMATIONS
    // ════════════════════════════════════════
    const revealElements = document.querySelectorAll('.reveal');

    if (revealElements.length > 0) {
        var observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        var revealObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        revealElements.forEach(function(el) {
            revealObserver.observe(el);
        });
    }

    // ════════════════════════════════════════
    // PRICING TABS
    // ════════════════════════════════════════
    const pricingTabs = document.querySelectorAll('.pricing-tab');
    const pricingContents = document.querySelectorAll('.pricing-content');

    pricingTabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var tabId = this.dataset.tab;

            // Remove active from all
            pricingTabs.forEach(function(t) { t.classList.remove('active'); });
            pricingContents.forEach(function(c) { c.classList.remove('active'); });

            // Add active to clicked
            this.classList.add('active');
            var targetContent = document.getElementById('tab-' + tabId);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });

    // ════════════════════════════════════════
    // FAQ ACCORDION
    // ════════════════════════════════════════
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(function(item) {
        var question = item.querySelector('.faq-question');
        if (question) {
            question.addEventListener('click', function() {
                var isActive = item.classList.contains('active');

                // Close all FAQ items
                faqItems.forEach(function(faq) {
                    faq.classList.remove('active');
                });

                // Toggle current
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        }
    });

    // ════════════════════════════════════════
    // GALLERY FILTERS
    // ════════════════════════════════════════
    const filterBtns = document.querySelectorAll('.filter-btn');
    const galleryItems = document.querySelectorAll('.masonry-item');

    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var filter = this.dataset.filter;

            // Update active button
            filterBtns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');

            // Filter items
            galleryItems.forEach(function(item) {
                if (filter === 'all' || item.dataset.category === filter) {
                    item.style.display = 'block';
                    item.style.animation = 'fadeIn 0.5s ease';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // ════════════════════════════════════════
    // LIGHTBOX
    // ════════════════════════════════════════
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxClose = document.getElementById('lightboxClose');
    const lightboxPrev = document.getElementById('lightboxPrev');
    const lightboxNext = document.getElementById('lightboxNext');
    let lightboxImages = [];
    let currentLightboxIndex = 0;

    // Collect all gallery images for lightbox
    var masonryItems = document.querySelectorAll('.masonry-item img');
    if (masonryItems.length > 0) {
        masonryItems.forEach(function(img, index) {
            lightboxImages.push(img.src);
            img.addEventListener('click', function() {
                openLightbox(index);
            });
        });
    }

    // Also attach to home gallery items
    var homeGalleryItems = document.querySelectorAll('.gallery-item img');
    if (homeGalleryItems.length > 0 && masonryItems.length === 0) {
        homeGalleryItems.forEach(function(img, index) {
            lightboxImages.push(img.src);
            img.style.cursor = 'pointer';
            img.addEventListener('click', function() {
                openLightbox(index);
            });
        });
    }

    function openLightbox(index) {
        if (!lightbox) return;
        currentLightboxIndex = index;
        lightboxImage.src = lightboxImages[index];
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }

    function showLightboxPrev() {
        currentLightboxIndex = (currentLightboxIndex - 1 + lightboxImages.length) % lightboxImages.length;
        lightboxImage.src = lightboxImages[currentLightboxIndex];
    }

    function showLightboxNext() {
        currentLightboxIndex = (currentLightboxIndex + 1) % lightboxImages.length;
        lightboxImage.src = lightboxImages[currentLightboxIndex];
    }

    if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
    if (lightboxPrev) lightboxPrev.addEventListener('click', showLightboxPrev);
    if (lightboxNext) lightboxNext.addEventListener('click', showLightboxNext);

    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) closeLightbox();
        });
    }

    // Keyboard navigation for lightbox
    document.addEventListener('keydown', function(e) {
        if (!lightbox || !lightbox.classList.contains('active')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') showLightboxPrev();
        if (e.key === 'ArrowRight') showLightboxNext();
    });

    // ════════════════════════════════════════
    // CONTACT FORM (AJAX)
    // ════════════════════════════════════════
    var contactForm = document.getElementById('EventsHouseContactForm');
    var formMessage = document.getElementById('formMessage');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var submitBtn = document.getElementById('submitBtn');
            var originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = 'A enviar... ⏳';
            submitBtn.disabled = true;

            var formData = new FormData(contactForm);

            // Check if we have AJAX URL (WordPress context)
            var ajaxUrl = (typeof EventsHouseData !== 'undefined') ? EventsHouseData.ajaxUrl : '';

            if (ajaxUrl) {
                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData,
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        showFormMessage('success', data.data.message);
                        contactForm.reset();
                    } else {
                        showFormMessage('error', data.data.message || 'Erro ao enviar. Tente novamente.');
                    }
                })
                .catch(function() {
                    showFormMessage('error', 'Erro de conexão. Tente novamente mais tarde.');
                })
                .finally(function() {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            } else {
                // Non-WordPress demo mode
                setTimeout(function() {
                    showFormMessage('success', 'Mensagem enviada com sucesso! Entraremos em contacto brevemente. 🎉');
                    contactForm.reset();
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 1500);
            }
        });
    }

    function showFormMessage(type, message) {
        if (!formMessage) return;
        formMessage.className = 'form-message ' + type;
        formMessage.textContent = message;
        formMessage.style.display = 'block';

        // Scroll to message
        formMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });

        // Auto-hide after 8s
        setTimeout(function() {
            formMessage.style.display = 'none';
        }, 8000);
    }

    // ════════════════════════════════════════
    // WOOCOMMERCE: produto tipo evento — mostrar campos «extra»
    // ════════════════════════════════════════
    // ════════════════════════════════════════
    // SMOOTH SCROLL for anchor links
    // ════════════════════════════════════════
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            var targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            var target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                var headerHeight = header ? header.offsetHeight : 80;
                var targetTop = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                
                window.scrollTo({
                    top: targetTop,
                    behavior: 'smooth'
                });
            }
        });
    });

    // ════════════════════════════════════════
    // PARALLAX EFFECT on Hero shapes
    // ════════════════════════════════════════
    var shapes = document.querySelectorAll('.hero-shape');
    if (shapes.length > 0) {
        window.addEventListener('scroll', function() {
            var scrolled = window.pageYOffset;
            shapes.forEach(function(shape, i) {
                var speed = (i + 1) * 0.03;
                shape.style.transform = 'translateY(' + (scrolled * speed) + 'px)';
            });
        }, { passive: true });
    }
});
