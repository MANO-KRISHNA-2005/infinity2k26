import { auth, googleProvider } from './firebase-config-module.js';
import {
    signOut,
    onAuthStateChanged,
    signInWithPopup
} from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

document.addEventListener("DOMContentLoaded", () => {
    // --- FIREBASE AUTH LOGIC ---

    // Google Sign-In
    const googleSignInBtn = document.getElementById('googleSignInBtn');
    if (googleSignInBtn) {
        googleSignInBtn.addEventListener('click', async (e) => {
            e.preventDefault();

            // Prevent multiple clicks
            if (googleSignInBtn.disabled) return;

            const originalText = googleSignInBtn.innerHTML;
            googleSignInBtn.innerHTML = `<i class="bi bi-hourglass-split"></i> Signing in...`;
            googleSignInBtn.disabled = true;
            googleSignInBtn.style.opacity = "0.7";
            googleSignInBtn.style.cursor = "not-allowed";

            try {
                const result = await signInWithPopup(auth, googleProvider);
                const user = result.user;
                console.log("Google Sign-In Success:", user);
                window.location.href = "index.html";
            } catch (error) {
                console.error("Google Sign-In Error:", error);
                // Re-enable if it was a user cancellation, otherwise alert
                if (error.code !== 'auth/popup-closed-by-user') {
                    alert(`Google Sign-In Failed: ${error.message}`);
                }
                // Reset button
                googleSignInBtn.innerHTML = originalText;
                googleSignInBtn.disabled = false;
                googleSignInBtn.style.opacity = "1";
                googleSignInBtn.style.cursor = "pointer";
            }
        });
    }

    // Auth State Observer
    onAuthStateChanged(auth, (user) => {
        const currentPath = window.location.pathname;
        const isAuthPage = currentPath.includes("auth.html");

        if (user) {
            console.log("User is signed in:", user.displayName);

            // 1. Redirect if on Auth Page
            if (isAuthPage) {
                window.location.href = "index.html";
                return;
            }

            // 2. Update Navbar on other pages (Home, etc.)
            updateNavbarForLoggedInUser(user);

        } else {
            console.log("User is signed out");

            // 1. No redirect needed from auth page (user is supposed to be there)

            // 2. Update Navbar to show "Sign Up"
            updateNavbarForLoggedOutUser();

            // 3. Protect Dashboard
            if (currentPath.includes("dashboard.html")) {
                window.location.href = "auth.html";
            }
        }
    });

    function updateNavbarForLoggedInUser(user) {
        const navLinks = document.getElementById('navLinks');
        const signUpBtn = document.querySelector('.nav-btn[href="auth.html"]');

        // Remove existing Sign Up button if it exists
        if (signUpBtn) {
            signUpBtn.remove();
        }

        // Check if dropdown already exists to avoid duplicates
        if (document.getElementById('userDropdown')) return;

        // Use User Photo if available, otherwise default icon
        const profileDisplay = user.photoURL
            ? `<img src="${user.photoURL}" class="user-avatar-img" alt="Profile">`
            : `<i class="bi bi-person-circle profile-icon"></i>`;

        // Create Dropdown HTML
        const dropdownHtml = `
            <div class="user-dropdown-container" id="userDropdown">
                <div class="nav-btn profile-btn" id="profileBtn">
                    ${profileDisplay}
                    <span class="profile-name">${user.displayName ? user.displayName.split(' ')[0] : 'User'}</span>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="dropdown-menu" id="dropdownMenu">
                    <div class="dropdown-header">
                        <p class="user-fullname">${user.displayName || 'User'}</p>
                        <p class="user-email">${user.email}</p>
                    </div>
                    <a href="dashboard.html" class="dropdown-item"><i class="bi bi-speedometer2"></i> Dashboard</a>
                    <hr>
                    <a href="#" class="dropdown-item" id="logoutBtn"><i class="bi bi-box-arrow-right"></i> Sign Out</a>
                </div>
            </div>
        `;

        // Append to nav links
        if (navLinks) {
            navLinks.insertAdjacentHTML('beforeend', dropdownHtml);
        }

        // Add Event Listeners for Dropdown
        const userDropdown = document.getElementById('userDropdown');
        const profileBtn = document.getElementById('profileBtn');
        const dropdownMenu = document.getElementById('dropdownMenu');
        const logoutBtn = document.getElementById('logoutBtn');

        if (userDropdown && dropdownMenu) {
            // Mobile: Click to toggle
            profileBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropdownMenu.classList.toggle('show');
            });

            // Desktop: Hover support (CSS handles most, but JS ensures class sync)
            userDropdown.addEventListener('mouseenter', () => {
                dropdownMenu.classList.add('show');
            });
            userDropdown.addEventListener('mouseleave', () => {
                dropdownMenu.classList.remove('show');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!userDropdown.contains(e.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });
        }

        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                signOut(auth).then(() => {
                    console.log("User signed out");
                    window.location.reload(); // Refresh to update UI
                }).catch((error) => {
                    console.error("Sign Out Error", error);
                });
            });
        }
    }

    function updateNavbarForLoggedOutUser() {
        const userDropdown = document.getElementById('userDropdown');
        if (userDropdown) {
            userDropdown.remove();
        }

        const navLinks = document.getElementById('navLinks');
        // Check if Sign Up button already exists
        const existingAuthBtn = document.querySelector('.nav-btn[href="auth.html"]');

        if (!existingAuthBtn && navLinks) {
            const signUpBtn = document.createElement('a');
            signUpBtn.href = "auth.html";
            signUpBtn.className = "nav-btn";
            signUpBtn.textContent = "Sign In";
            navLinks.appendChild(signUpBtn);
        }
    }
});
