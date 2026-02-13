// Initialize Firebase (Compat)
const auth = firebase.auth();
const db = firebase.firestore();

const googleProvider = new firebase.auth.GoogleAuthProvider();
const signInBtn = document.getElementById('googleSignInBtn');
const alumniForm = document.getElementById('alumniForm');
const submitBtn = document.getElementById('submitBtn');
const authStatus = document.getElementById('authStatus');

let currentUser = null;

// Google Sign-In
signInBtn.addEventListener('click', () => {
    auth.signInWithPopup(googleProvider)
        .then((result) => {
            currentUser = result.user;
            authStatus.innerText = `Signed in as: ${currentUser.displayName}`;

            // Pre-fill form
            document.getElementById('alumniName').value = currentUser.displayName || '';
            document.getElementById('alumniEmail').value = currentUser.email || '';

            // Enable submit
            submitBtn.disabled = false;
            signInBtn.style.display = 'none';
        })
        .catch((error) => {
            console.error(error);
            alert("Sign in failed: " + error.message);
        });
});

// Form Submission
alumniForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!currentUser) {
        alert("Please sign in with Google first.");
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerText = "PROCESSING...";

    const alumniData = {
        user_id: currentUser.uid,
        name: document.getElementById('alumniName').value,
        email: document.getElementById('alumniEmail').value,
        phone: document.getElementById('alumniPhone').value,
        grad_year: document.getElementById('alumniYear').value,
        timestamp: firebase.firestore.FieldValue.serverTimestamp()
    };

    try {
        // 1. Save to Firestore
        const docRef = await db.collection('alumni_registrations').add(alumniData);
        alumniData.firebase_doc_id = docRef.id;

        // 2. Save to MySQL via PHP
        const response = await fetch('php/alumni_save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(alumniData)
        });

        const result = await response.json();

        if (result.success) {
            alumniForm.style.display = 'none';
            document.querySelector('.divider-text').style.display = 'none';
            document.getElementById('successMsg').style.display = 'block';
        } else {
            throw new Error(result.message || "MySQL storage failed");
        }

    } catch (error) {
        console.error(error);
        alert("Registration failed: " + error.message);
        submitBtn.disabled = false;
        submitBtn.innerText = "CONFIRM REGISTRATION";
    }
});
