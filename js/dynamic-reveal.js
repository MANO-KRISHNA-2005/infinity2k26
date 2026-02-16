document.addEventListener('DOMContentLoaded', () => {
    // Register ScrollTrigger
    if (typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
    }

    // --- Dual Split Animations ---
    gsap.from(".split-left", {
        scrollTrigger: {
            trigger: ".dual-split-section",
            start: "top 80%",
            toggleActions: "play none none reverse"
        },
        x: -50,
        opacity: 0,
        duration: 1,
        ease: "power2.out"
    });

    gsap.from(".split-right", {
        scrollTrigger: {
            trigger: ".dual-split-section",
            start: "top 80%",
            toggleActions: "play none none reverse"
        },
        x: 50,
        opacity: 0,
        duration: 1,
        ease: "power2.out"
    });

    // --- Elevated E-Magazine Animations ---
    const magTL = gsap.timeline({
        scrollTrigger: {
            trigger: ".magazine-section",
            start: "top 60%",
            toggleActions: "play none none reverse"
        }
    });

    magTL.from(".magazine-mockup", {
        rotateY: -90,
        opacity: 0,
        duration: 2,
        ease: "power3.out"
    })
        .to(".scanning-beam", {
            opacity: 1,
            duration: 0.5
        }, "-=1")
        .to(".scanning-beam", {
            top: "100%",
            duration: 2,
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut"
        }, "-=0.5");

    // Digital Stamp Animation - "Stamping" Effect with independent Loop
    const stampTL = gsap.timeline({
        repeat: -1,
        repeatDelay: 4,
        scrollTrigger: {
            trigger: ".magazine-section",
            start: "top 60%",
            toggleActions: "play none none reverse"
        }
    });

    stampTL.fromTo("#comingSoonStamp",
        { scale: 5, opacity: 0 },
        { scale: 1, opacity: 1, duration: 0.6, ease: "power4.in" }
    )
        .fromTo("#comingSoonStamp",
            { scale: 1.1 },
            { scale: 1, duration: 0.4, ease: "back.out(2)" }
        )
        .to("#comingSoonStamp", {
            opacity: 0,
            duration: 0.5,
            delay: 3 // Stay visible for 3 seconds
        });

    // Terminal "Hacking" Typing Effect
    const terminalLines = document.querySelectorAll(".terminal-line");

    terminalLines.forEach((line, index) => {
        const text = line.innerText;
        line.innerText = "";

        const chars = text.split("");
        chars.forEach(char => {
            const span = document.createElement("span");
            span.textContent = char;
            span.style.opacity = "0";
            span.style.display = "inline-block";
            line.appendChild(span);
        });

        gsap.to(line.querySelectorAll("span"), {
            scrollTrigger: {
                trigger: ".magazine-section",
                start: "top 70%",
            },
            opacity: 1,
            stagger: 0.05,
            duration: 0.05,
            ease: "none",
            delay: index * 0.8 // Wait for previous line to finish typing
        });
    });
});
