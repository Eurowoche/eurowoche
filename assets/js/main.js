// === Menu Toggle ===
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menu-toggle');
    const navOverlay = document.getElementById('nav-overlay');
    const navClose = document.getElementById('nav-close');
    
    if (menuToggle && navOverlay) {
        menuToggle.addEventListener('click', function() {
            navOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        if (navClose) {
            navClose.addEventListener('click', function() {
                navOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        // Close on link click
        navOverlay.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                navOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
        
        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && navOverlay.classList.contains('active')) {
                navOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
    
    // === Header Scroll Effect ===
    const header = document.getElementById('site-header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
    
    // === Testimonials Slider ===
    const slides = document.querySelectorAll('.testimonial-slide');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.getElementById('testimonial-prev');
    const nextBtn = document.getElementById('testimonial-next');
    let currentSlide = 0;
    
    var testimonialsSection = document.querySelector('.testimonials-section');

    function showSlide(index) {
        if (slides.length === 0) return;
        slides.forEach(function(s) { s.classList.remove('active'); });
        dots.forEach(function(d) { d.classList.remove('active'); });
        currentSlide = (index + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
        // Hintergrundfarbe pro Slide wechseln
        var bg = slides[currentSlide].getAttribute('data-bg');
        if (testimonialsSection) {
            testimonialsSection.style.backgroundColor = bg || '';
        }
    }
    
    if (prevBtn) prevBtn.addEventListener('click', function() { showSlide(currentSlide - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function() { showSlide(currentSlide + 1); });
    dots.forEach(function(dot) {
        dot.addEventListener('click', function() {
            showSlide(parseInt(this.getAttribute('data-index')));
        });
    });
    
    // Auto-advance testimonials
    if (slides.length > 0) {
        setInterval(function() { showSlide(currentSlide + 1); }, 6000);
    }
    
    // === Archiv Search (mit rowspan-Support) ===
    var archivSearch = document.getElementById('archiv-search');
    var archivTable = document.getElementById('archiv-table');
    var archivCount = document.getElementById('archiv-result-count');
    if (archivSearch && archivTable) {
        // Gruppen sammeln: alle Rows mit gleicher nummer gehören zusammen
        var tbody = archivTable.querySelector('tbody');
        var allRows = Array.from(tbody.querySelectorAll('tr'));

        // Gruppen nach data-nummer bilden
        var groups = [];
        var currentGroup = null;
        allRows.forEach(function(row) {
            var nr = row.getAttribute('data-nummer');
            if (!currentGroup || currentGroup.nummer !== nr) {
                currentGroup = {
                    nummer: nr,
                    rows: [],
                    text: '',
                    jahr: row.getAttribute('data-jahr') || ''
                };
                groups.push(currentGroup);
            }
            currentGroup.rows.push(row);

            // Extrahiere Zellentext: textContent von allen Zellen
            var cells = Array.from(row.querySelectorAll('td'));
            cells.forEach(function(cell) {
                currentGroup.text += ' ' + cell.textContent;
            });
        });

        // Normalisiere den Text: lowercase und extra Leerzeichen entfernen
        groups.forEach(function(group) {
            group.text = group.text.toLowerCase().trim();
        });

        archivSearch.addEventListener('input', function() {
            var query = this.value.toLowerCase().trim();
            var visibleGroups = 0;
            var visibleRows = 0;

            groups.forEach(function(group) {
                // Suche im gesammelten Text dieser Gruppe
                var match = !query || group.text.indexOf(query) !== -1;

                group.rows.forEach(function(row) {
                    row.classList.toggle('archiv-hidden', !match);
                });
                if (match) {
                    visibleGroups++;
                    visibleRows += group.rows.length;
                }
            });

            if (archivCount) {
                if (query) {
                    archivCount.textContent = visibleGroups + ' Eurowoche' + (visibleGroups !== 1 ? 'n' : '') + ' gefunden';
                } else {
                    archivCount.textContent = '';
                }
            }
        });
    }
    
    // === Mosaik Gallery — absolute positioning from data attributes ===
    const galleryTrack = document.querySelector('.gallery-track');
    const galleryPrev = document.getElementById('gallery-prev');
    const galleryNext = document.getElementById('gallery-next');

    if (galleryTrack && galleryNext) {
        var galleryHeight = 384;
        var gap = 5;
        var trackWidth = 0;

        // Position all mosaic items from data attributes
        var mosaicItems = galleryTrack.querySelectorAll('.mosaic-item');
        mosaicItems.forEach(function(item) {
            var x = parseInt(item.getAttribute('data-x')) || 0;
            var yPct = parseInt(item.getAttribute('data-y')) || 0;
            var w = parseInt(item.getAttribute('data-w')) || 200;
            var hPct = parseInt(item.getAttribute('data-h')) || 50;

            var top = Math.round(galleryHeight * yPct / 100);
            var height = Math.round(galleryHeight * hPct / 100) - gap;

            item.style.left = x + 'px';
            item.style.top = top + 'px';
            item.style.width = w + 'px';
            item.style.height = height + 'px';

            var right = x + w;
            if (right > trackWidth) trackWidth = right;
        });

        galleryTrack.style.width = trackWidth + 'px';

        var galleryPos = 0;
        var slider = document.querySelector('.gallery-slider');

        function getMaxScroll() {
            return Math.max(0, trackWidth - slider.clientWidth);
        }

        function updateArrows() {
            var max = getMaxScroll();
            if (galleryPrev) galleryPrev.style.opacity = galleryPos <= 0 ? '0.3' : '1';
            if (galleryNext) galleryNext.style.opacity = galleryPos >= max ? '0.3' : '1';
            if (galleryPrev) galleryPrev.style.pointerEvents = galleryPos <= 0 ? 'none' : 'auto';
            if (galleryNext) galleryNext.style.pointerEvents = galleryPos >= max ? 'none' : 'auto';
        }

        function scrollGallery(direction) {
            var step = slider.clientWidth * 0.5;
            galleryPos += direction * step;
            galleryPos = Math.max(0, Math.min(galleryPos, getMaxScroll()));
            galleryTrack.style.transform = 'translateX(-' + galleryPos + 'px)';
            updateArrows();
        }

        galleryNext.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); scrollGallery(1); });
        galleryPrev.addEventListener('click', function(e) { e.preventDefault(); e.stopPropagation(); scrollGallery(-1); });
        updateArrows();

        // Touch/swipe support
        var touchStartX = 0;
        slider.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
        }, { passive: true });
        slider.addEventListener('touchend', function(e) {
            var diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 50) {
                scrollGallery(diff > 0 ? 1 : -1);
            }
        }, { passive: true });

        // === Lightbox: click on gallery image to view fullscreen ===
        var lightbox = document.createElement('div');
        lightbox.className = 'gallery-lightbox';
        lightbox.innerHTML = '<div class="lightbox-backdrop"></div><img class="lightbox-img" src="" alt=""><button class="lightbox-close" aria-label="Schließen">&times;</button><button class="lightbox-prev" aria-label="Zurück">&#8249;</button><button class="lightbox-next" aria-label="Weiter">&#8250;</button>';
        document.body.appendChild(lightbox);

        var lightboxImg = lightbox.querySelector('.lightbox-img');
        var lightboxClose = lightbox.querySelector('.lightbox-close');
        var lightboxPrevBtn = lightbox.querySelector('.lightbox-prev');
        var lightboxNextBtn = lightbox.querySelector('.lightbox-next');
        var galleryImages = Array.from(galleryTrack.querySelectorAll('.mosaic-item img'));
        var lightboxIndex = 0;

        function openLightbox(index) {
            lightboxIndex = index;
            lightboxImg.src = galleryImages[index].src;
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        function lightboxNav(dir) {
            lightboxIndex = (lightboxIndex + dir + galleryImages.length) % galleryImages.length;
            lightboxImg.src = galleryImages[lightboxIndex].src;
        }

        galleryImages.forEach(function(img, i) {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function(e) {
                e.stopPropagation();
                openLightbox(i);
            });
        });

        lightboxClose.addEventListener('click', closeLightbox);
        lightbox.querySelector('.lightbox-backdrop').addEventListener('click', closeLightbox);
        lightboxPrevBtn.addEventListener('click', function() { lightboxNav(-1); });
        lightboxNextBtn.addEventListener('click', function() { lightboxNav(1); });

        document.addEventListener('keydown', function(e) {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') lightboxNav(-1);
            if (e.key === 'ArrowRight') lightboxNav(1);
        });
    }

    // === Märkte Slider Navigation + Lightbox ===
    document.querySelectorAll('.maerkte-slider-wrapper').forEach(function(wrapper) {
        var track = wrapper.querySelector('.maerkte-slider-track');
        var prevBtn = wrapper.querySelector('.maerkte-slider-prev');
        var nextBtn = wrapper.querySelector('.maerkte-slider-next');
        if (!track) return;

        // Scroll buttons
        var scrollAmount = 600;
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });
        }
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });
        }

        // Lightbox
        var imgArray = Array.from(track.querySelectorAll('.maerkte-slider-item img'));
        if (imgArray.length === 0) return;

        // Globale Lightbox erstellen (einmalig)
        var lb = document.getElementById('maerkte-lightbox');
        if (!lb) {
            lb = document.createElement('div');
            lb.id = 'maerkte-lightbox';
            lb.innerHTML = [
                '<div class="lb-backdrop"></div>',
                '<img class="lb-img" src="" alt="">',
                '<button class="lb-close" aria-label="Schließen">&times;</button>',
                '<button class="lb-prev" aria-label="Zurück">&#8249;</button>',
                '<button class="lb-next" aria-label="Weiter">&#8250;</button>'
            ].join('');
            document.body.appendChild(lb);

            // Globale Lightbox-Steuerung
            var lbImg = lb.querySelector('.lb-img');
            var lbAllImages = [];
            var lbIdx = 0;

            function lbOpen(arr, idx) {
                lbAllImages = arr;
                lbIdx = idx;
                lbImg.src = lbAllImages[lbIdx];
                lb.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            function lbClose() {
                lb.classList.remove('active');
                document.body.style.overflow = '';
            }
            function lbNav(dir) {
                lbIdx = (lbIdx + dir + lbAllImages.length) % lbAllImages.length;
                lbImg.src = lbAllImages[lbIdx];
            }

            lb.querySelector('.lb-backdrop').addEventListener('click', lbClose);
            lb.querySelector('.lb-close').addEventListener('click', lbClose);
            lb.querySelector('.lb-prev').addEventListener('click', function(e) { e.stopPropagation(); lbNav(-1); });
            lb.querySelector('.lb-next').addEventListener('click', function(e) { e.stopPropagation(); lbNav(1); });
            document.addEventListener('keydown', function(e) {
                if (!lb.classList.contains('active')) return;
                if (e.key === 'Escape') lbClose();
                if (e.key === 'ArrowLeft') lbNav(-1);
                if (e.key === 'ArrowRight') lbNav(1);
            });

            // Funktion global verfügbar machen
            window.lbOpen = lbOpen;
        }

        // Jedes Bild klickbar machen
        imgArray.forEach(function(img, i) {
            img.parentElement.addEventListener('click', function(e) {
                e.stopPropagation();
                var srcs = imgArray.map(function(im) { return im.src; });
                window.lbOpen(srcs, i);
            });
        });
    });

    // Lightbox auch für andere Galerien (.gallery-item)
    document.querySelectorAll('.gallery').forEach(function(gallery) {
        if (gallery.closest('.maerkte-slider-track')) return; // already handled
        var items = Array.from(gallery.querySelectorAll('.gallery-item img'));
        items.forEach(function(img, i) {
            img.style.cursor = 'zoom-in';
            img.addEventListener('click', function(e) {
                e.stopPropagation();
                var srcs = items.map(function(im) { return im.src; });
                if (window.lbOpen) window.lbOpen(srcs, i);
            });
        });
    });

    // === Lazy Loading ===
    if ('IntersectionObserver' in window) {
        const imgObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    imgObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(function(img) {
            imgObserver.observe(img);
        });
    }
});
