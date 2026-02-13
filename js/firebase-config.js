// Firebase Configuration (Compat Version)
// This uses the global 'firebase' namespace loaded via script tags in publicity.php

const firebaseConfig = {
    apiKey: "AIzaSyCi-4p4cyahglxocOsHIG2oT6O05nfpOtk",
    authDomain: "infinity-2k26.firebaseapp.com",
    projectId: "infinity-2k26",
    storageBucket: "infinity-2k26.firebasestorage.app",
    messagingSenderId: "620576782678",
    appId: "1:620576782678:web:5d6f36a182465d26ee7ef7"
};

// Initialize Firebase
if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
} else {
    firebase.app(); // if already initialized, use that one
}

// Ensure db and auth are available globally or initialized here if needed
// In publicity.js, we call `const db = firebase.firestore();` which works
// because firebase.initializeApp runs here first.
