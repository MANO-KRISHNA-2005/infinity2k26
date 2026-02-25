document.addEventListener("DOMContentLoaded", () => {
    window.scrollTo(0, 0);

    // --- 1. PRELOADER LOGIC ---
    const path = document.getElementById('progress-path');
    const percentageElement = document.getElementById('percentage');
    const preloader = document.getElementById('preloader');
    const mainContent = document.getElementById('main-content');

    const pathLength = path.getTotalLength();
    path.style.strokeDasharray = pathLength;
    path.style.strokeDashoffset = pathLength;

    let progress = 0;
    const loadingInterval = setInterval(() => {
        progress++;
        percentageElement.innerText = progress + "%";
        path.style.strokeDashoffset = pathLength - (pathLength * (progress / 100));

        if (progress >= 100) {
            clearInterval(loadingInterval);
            setTimeout(() => {
                // Fade out preloader
                preloader.style.opacity = '0';
                setTimeout(() => {
                    preloader.style.display = 'none';
                    document.body.classList.remove('no-scroll');
                    // Trigger the ORIGINAL shatter animation
                    triggerShatterSequence();
                }, 800);
            }, 500);
        }
    }, 35);


    // --- 2. ORIGINAL SHATTER ANIMATION LOGIC ---
    const shatterBox = document.getElementById('shatterBox');
    // Fragment generation for the shatter effect
    for (let i = 0; i < 27; i++) {
        const shard = document.createElement('div');
        shard.className = 'shard';
        const p1 = Math.random() * 50;
        const p2 = Math.random() * 50 + 50;
        const p3 = Math.random() * 100;
        shard.style.clipPath = `polygon(${p1}% ${p3}%, ${p3}% ${p1}%, ${p2}% ${p2}%)`;
        shatterBox.appendChild(shard);
    }

    function triggerShatterSequence() {
        const shards = document.querySelectorAll('.shard');
        const logo6 = document.getElementById('logo6');
        const tagline = document.getElementById('tagline');

        shatterBox.classList.add('shaking');

        setTimeout(() => {
            shatterBox.classList.remove('shaking');
            shards.forEach(s => {
                const angle = Math.random() * Math.PI * 2;
                const velocity = 1000 + Math.random() * 1400;
                const x = Math.cos(angle) * velocity;
                const y = Math.sin(angle) * velocity;
                const r = (Math.random() - 0.5) * 1600;
                s.style.transform = `translate(${x}px, ${y}px) rotate(${r}deg) scale(0)`;
                s.style.opacity = "0";
            });

            setTimeout(() => {
                logo6.classList.add('drop-impact');
                tagline.style.opacity = "1";
                tagline.style.transform = "translateY(0)";

                // Reveal Timer after tagline
                setTimeout(() => {
                    const timerWrapper = document.getElementById('timerWrapper');
                    if (timerWrapper) {
                        timerWrapper.style.opacity = "1";
                        timerWrapper.style.transform = "translateY(0)";
                    }
                }, 800);

            }, 400);

        }, 800);
    }


    // --- 3. CASH PRIZE DECRYPTION ---
    const targetAmount = "50,000";
    const chars = "0123456789$#@&%*<>[]";
    const decryptEl = document.getElementById("decrypt-text");
    let decryptInterval = null;

    function startDecryption() {
        let iterations = 0;
        decryptEl.parentElement.classList.remove("flash");

        clearInterval(decryptInterval);
        decryptInterval = setInterval(() => {
            decryptEl.innerText = targetAmount.split("").map((char, index) => {
                if (index < iterations) return targetAmount[index];
                return chars[Math.floor(Math.random() * chars.length)];
            }).join("");

            if (iterations >= targetAmount.length) {
                clearInterval(decryptInterval);
                decryptEl.parentElement.classList.add("flash");
            }
            iterations += 1 / 3;
        }, 50);
    }

    // Function to handle visibility
    function initPrizeObserver() {
        const prizeSection = document.querySelector('.prize-section');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Run immediately when visible
                    startDecryption();

                    // Set interval to repeat every 6 seconds while visible
                    const repeatInterval = setInterval(() => {
                        if (entry.isIntersecting) {
                            startDecryption();
                        } else {
                            clearInterval(repeatInterval);
                        }
                    }, 6000);
                }
            });
        }, { threshold: 0.5 }); // Triggers when 50% of the section is visible

        observer.observe(prizeSection);
    }

    // Initialize the prize observer after preloader
    setTimeout(() => {
        initPrizeObserver();
    }, 2000);


    // --- 4. SCROLL & PARALLAX LOGIC ---
    const navItems = document.querySelectorAll('.nav-item');
    window.addEventListener('scroll', () => {
        let y = window.pageYOffset;

        if (document.getElementById('heroBg'))
            document.getElementById('heroBg').style.transform = `scale(${1.1 - y * 0.0004}) translateY(${y * 0.1}px)`;

        const sections = document.querySelectorAll('section');
        let current = "";
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (y >= sectionTop - 150) {
                current = section.getAttribute('id');
            }
        });

        navItems.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('href').includes(current)) {
                item.classList.add('active');
            }
        });

        const aboutSection = document.getElementById('about');
        if (aboutSection) {
            const aboutOffset = aboutSection.offsetTop;
            const rel = y - aboutOffset + window.innerHeight;
            if (rel > 0) {
                document.getElementById('aboutBg').style.transform = `scale(${1 + rel * 0.00015}) translateY(${-rel * 0.02}px)`;
            }
        }
    });



    // --- 5. COUNTDOWN TIMER ---
    const eventDate = new Date("March 5, 2026 00:00:00").getTime();

    const countdown = setInterval(() => {
        const now = new Date().getTime();
        const distance = eventDate - now;

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        const dEl = document.getElementById("days");
        const hEl = document.getElementById("hours");
        const mEl = document.getElementById("minutes");
        const sEl = document.getElementById("seconds");

        if (dEl) dEl.innerText = days < 10 ? "0" + days : days;
        if (hEl) hEl.innerText = hours < 10 ? "0" + hours : hours;
        if (mEl) mEl.innerText = minutes < 10 ? "0" + minutes : minutes;
        if (sEl) sEl.innerText = seconds < 10 ? "0" + seconds : seconds;

        if (distance < 0) {
            clearInterval(countdown);
            document.querySelector(".countdown-container").innerHTML = "EVENT STARTED";
        }
    }, 1000);

    // --- 6. NAVIGATION TOGGLE ---
    document.getElementById('hamburger').onclick = () => {
        const links = document.getElementById('navLinks');
        links.classList.toggle('active');
    };

    // --- 6. SPONSORS SECTION INTERACTIONS ---
    // 3D Tilt & Spotlight Effect for Sponsor Cards
    const cards = document.querySelectorAll('.sponsor-card');

    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            // Update Spotlight Position
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);

            // Calculate Tilt
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const maxRotate = 10;

            const rotateY = ((x - centerX) / centerX) * maxRotate;
            const rotateX = ((centerY - y) / centerY) * maxRotate; // Invert Y axis for natural tilt

            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
        });
    });

    // Magnetic Button Effect
    const magneticBtn = document.querySelector('.cta-button');

    if (magneticBtn) {
        magneticBtn.addEventListener('mousemove', (e) => {
            const rect = magneticBtn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;

            // Move button slightly towards cursor (magnetic pull)
            const strength = 0.4;
            magneticBtn.style.transform = `translate(${x * strength}px, ${y * strength}px)`;
            magneticBtn.style.boxShadow = `0 0 25px rgba(255, 215, 0, 0.4)`; // Enhanced glow on interaction
        });

        magneticBtn.addEventListener('mouseleave', () => {
            magneticBtn.style.transform = 'translate(0, 0)';
            magneticBtn.style.boxShadow = '0 0 10px rgba(255, 215, 0, 0.2)'; // Return to normal
        });
    }

    // Alumni Registration Button is now handled by direct link in HTML

    // Scroll Animation Observer for Sponsors Section
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('reveal-active');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.sponsor-section').forEach(section => {
        observer.observe(section);
    });

    // --- 7. OUR LEGACY CAROUSEL ---
    // --- 7. OUR LEGACY CAROUSEL ---
    let p = 0, t = 0;
    const legacyCards = document.querySelectorAll(".carousel-item");

    function legacyLoop() {
        t += 0.006;
        p += (t - p) * 0.035;

        legacyCards.forEach((el, i) => {
            const off = i - (p % legacyCards.length);
            const d = Math.abs(off);

            el.classList.toggle("active", d < .4);
            el.style.setProperty("--x", `${off * 260}px`);
            el.style.setProperty("--y", `${d * 45}px`);
            el.style.setProperty("--z", `${-d * 200}px`);
            el.style.setProperty("--rot", `${off * 20}deg`);
            el.style.setProperty("--sc", `${1 - d * .13}`);
            el.style.setProperty("--zi", 100 - d * 10);
            el.style.setProperty("--op", `${1 - d * .55}`);
            el.querySelector("img").style.setProperty("--imgx", `${off * -24}px`);
        });

        requestAnimationFrame(legacyLoop);
    }

    // Start the legacy carousel animation
    if (legacyCards.length > 0) {
        legacyLoop();
    }

    // --- 8. OUR JOURNEY (SCROLL LOCK + ZOOM OUT) ---
    gsap.registerPlugin(ScrollTrigger);

    const journeyImages = gsap.utils.toArray(".journey-image");

    const journeyTimeline = gsap.timeline({
        scrollTrigger: {
            trigger: ".journey-section",
            start: "top top",
            end: `+=${journeyImages.length * 100}%`,
            pin: ".journey-pin",
            scrub: true,
            snap: {
                snapTo: 1 / journeyImages.length,
                duration: 0.4,
                ease: "power1.inOut"
            }
        }
    });

    // Aggressive title shrink at the very beginning of the scroll
    journeyTimeline.to(".journey-title", {
        scale: 0.2,
        y: -160,
        duration: 0.5,
        ease: "power2.out",
        transformOrigin: "center top"
    }, 0);

    journeyImages.forEach((img, i) => {
        const startTime = (i === 0) ? 0.4 : i + 0.4;
        journeyTimeline
            .fromTo(img,
                { opacity: 0, scale: 1.1, xPercent: -50, yPercent: -50 },
                { opacity: 1, scale: 1, xPercent: -50, yPercent: -50, duration: 1 },
                startTime
            )
            .to(img,
                { opacity: 0, scale: 0.9, xPercent: -50, yPercent: -50, duration: 1 },
                startTime + 1.2
            );
    });

    journeyTimeline.to([".journey-title", ".journey-caption"], { autoAlpha: 0, duration: 0.5 });
    // --- 9. TEAM JACKPOT (AUTO SCROLL) ---
    const team = [
        { role: "Secretary", name: "VISAAL MUTHUKUMAR" },
        { role: "Secretary", name: "SOFIYA" },
        { role: "Executive Secretary", name: "MANO KRISHNA" },
        { role: "Executive Secretary", name: "THEEKSHANA" },
        { role: "Treasurer", name: "SHARVESH" },
        { role: "Treasurer", name: "SANTHIYA" },
        { role: "Technical Head", name: "SHIVAANI" },
        { role: "Technical Head", name: "MADHU HARITHA" },
        { role: "Co-Organizer ", name: "Karthikeyan" },
        { role: "Co-Organizer", name: "Dhana Harani" }
    ];

    const desList = document.getElementById('designation-list');
    const nameList = document.getElementById('name-list');

    if (desList && nameList) {
        const itemHeight = 100;   // must match CSS
        const repeats = 20;
        const spinDuration = 4000; // 4s jackpot roll
        const pauseDuration = 2500; // pause after stop

        function initTeam() {
            let desHtml = '';
            let nameHtml = '';

            for (let i = 0; i < repeats; i++) {
                team.forEach(person => {
                    desHtml += `<li>${person.role}</li>`;
                    nameHtml += `<li>${person.name}</li>`;
                });
            }

            desList.innerHTML = desHtml;
            nameList.innerHTML = nameHtml;

            // Start right list from bottom
            const startRight = -((repeats - 1) * team.length * itemHeight);
            nameList.style.transition = 'none';
            nameList.style.transform = `translateY(${startRight}px)`;
        }

        let currentTeamIndex = 0;

        function spinTeam() {
            const targetIndex = currentTeamIndex;
            currentTeamIndex = (currentTeamIndex + 1) % team.length;

            // LEFT rolls downward
            const leftSet = repeats - 2;
            const leftOffset = -((leftSet * team.length + targetIndex) * itemHeight);

            // RIGHT rolls upward
            const rightSet = 1;
            const rightOffset = -((rightSet * team.length + targetIndex) * itemHeight);

            desList.style.transition =
                `transform ${spinDuration}ms cubic-bezier(0.1, 0, 0.1, 1)`;
            nameList.style.transition =
                `transform ${spinDuration}ms cubic-bezier(0.1, 0, 0.1, 1)`;

            desList.style.transform = `translateY(${leftOffset}px)`;
            nameList.style.transform = `translateY(${rightOffset}px)`;

            // Reset silently after stop (important for infinite loop)
            setTimeout(() => {
                desList.style.transition = 'none';
                nameList.style.transition = 'none';

                desList.style.transform =
                    `translateY(-${targetIndex * itemHeight}px)`;
                nameList.style.transform =
                    `translateY(-${targetIndex * itemHeight}px)`;
            }, spinDuration + 50);
        }

        initTeam();

        // Auto jackpot loop starting after 1s
        setTimeout(() => {
            spinTeam();
            setInterval(spinTeam, spinDuration + pauseDuration);
        }, 1000);
    }
    // --- 10. EVENTS SECTION (FILTER + HYBRID CAROUSEL) ---
    const eventsCarousel = document.getElementById('eventsCarousel');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const eventCards = document.querySelectorAll('.event-card');

    if (eventsCarousel && eventCards.length > 0) {
        let scrollAmount = 0;
        let isAutoScrolling = true;
        let autoScrollTimer = null;
        let currentFilter = 'all';

        // --- Helper: Get Responsive Step Size ---
        function getStepSize() {
            if (window.innerWidth <= 768) return 315; // card width + gap on mobile
            return 412; // card width + gap on desktop
        }

        // --- Helper: Update UI for Scroll Mode ---
        function updateCarouselState() {
            const visibleCards = Array.from(eventCards).filter(c => c.style.display !== 'none');
            const visibleCount = visibleCards.length;

            // Reset transform when changing categories
            scrollAmount = 0;
            eventsCarousel.style.transform = `translateX(0)`;

            if (visibleCount <= 3 && window.innerWidth > 1024) {
                eventsCarousel.classList.add('centered-mode');
                eventsCarousel.classList.remove('scrolling-mode');
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
                isAutoScrolling = false;
            } else {
                eventsCarousel.classList.remove('centered-mode');
                if (prevBtn) prevBtn.style.display = 'flex';
                if (nextBtn) nextBtn.style.display = 'flex';

                // Only use scrolling-mode (infinite CSS animation) for 'all'
                if (currentFilter === 'all' && visibleCount > 3) {
                    eventsCarousel.classList.add('scrolling-mode');
                    isAutoScrolling = true;
                } else {
                    eventsCarousel.classList.remove('scrolling-mode');
                    isAutoScrolling = false;
                }
            }
        }

        // --- Manual Navigation with Infinite Loop ---
        function manualShift(direction) {
            eventsCarousel.classList.remove('scrolling-mode');
            isAutoScrolling = false;

            const step = getStepSize();
            const containerWidth = eventsCarousel.parentElement.clientWidth;
            const scrollWidth = eventsCarousel.scrollWidth;
            const maxScroll = -(scrollWidth - containerWidth);

            if (direction === 'next') {
                scrollAmount -= step;
                // Infinite Loop: If we go past the end, jump back to start
                if (scrollAmount < maxScroll - 50) {
                    scrollAmount = 0;
                }
            } else {
                scrollAmount += step;
                // Infinite Loop: If we go past the start, jump to the end
                if (scrollAmount > 50) {
                    scrollAmount = maxScroll;
                }
            }

            eventsCarousel.style.transform = `translateX(${scrollAmount}px)`;

            // Resume auto-scroll after 10s of inactivity if filter is 'all'
            clearTimeout(autoScrollTimer);
            autoScrollTimer = setTimeout(() => {
                if (currentFilter === 'all') {
                    updateCarouselState();
                }
            }, 10000);
        }

        if (nextBtn && prevBtn) {
            nextBtn.addEventListener('click', () => manualShift('next'));
            prevBtn.addEventListener('click', () => manualShift('prev'));
        }

        // --- Filtering Logic ---
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const filter = btn.getAttribute('data-filter');
                currentFilter = filter;

                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                eventCards.forEach(card => {
                    if (filter === 'all' || card.getAttribute('data-category') === filter) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });

                updateCarouselState();
            });
        });

        // Initialize state
        updateCarouselState();
    }
});