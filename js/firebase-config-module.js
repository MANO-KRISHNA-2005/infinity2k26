// Firebase Configuration (Modular Version)
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getAuth, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";
import { getFirestore } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";

const firebaseConfig = {
    apiKey: "AIzaSyCi-4p4cyahglxocOsHIG2oT6O05nfpOtk",
    authDomain: "infinity-2k26.firebaseapp.com",
    projectId: "infinity-2k26",
    storageBucket: "infinity-2k26.firebasestorage.app",
    messagingSenderId: "620576782678",
    appId: "1:620576782678:web:5d6f36a182465d26ee7ef7"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);
const googleProvider = new GoogleAuthProvider();

export { app, auth, db, googleProvider };
