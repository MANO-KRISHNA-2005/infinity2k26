import { auth, db } from './firebase-config-module.js';
import { onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";
import { collection, addDoc, serverTimestamp, query, where, getDocs, or } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("registrationModal");
    const closeModal = document.querySelector(".close-modal");
    const eventSelect = document.getElementById("eventSelect");
    const regName = document.getElementById("regName");
    const regEmail = document.getElementById("regEmail");
    const registrationForm = document.getElementById("eventRegistrationForm");

    let currentUser = null;

    // Monitor Auth State
    onAuthStateChanged(auth, (user) => {
        currentUser = user;
        if (user) {
            console.log("User detected for registration:", user.email);
            loadMyRegistrations();
        } else {
            const myRegsContainer = document.getElementById("myRegistrations");
            if (myRegsContainer) myRegsContainer.style.display = "none";
        }
    });

    const loadMyRegistrations = async () => {
        if (!currentUser) return;
        const myRegsContainer = document.getElementById("myRegistrations");
        const list = document.getElementById("registeredEventsList");
        if (!myRegsContainer || !list) return;

        try {
            const registrationsRef = collection(db, "registrations");
            // Query where user is leader
            const qLeader = query(registrationsRef, where("teamLeader.email", "==", currentUser.email));
            // Query where user is teammate
            const qTeammate = query(registrationsRef, where("teamMate.email", "==", currentUser.email));

            const [snapLeader, snapTeammate] = await Promise.all([getDocs(qLeader), getDocs(qTeammate)]);

            const allRegs = [];
            snapLeader.forEach(doc => allRegs.push({ id: doc.id, ...doc.data(), role: 'Leader' }));
            snapTeammate.forEach(doc => allRegs.push({ id: doc.id, ...doc.data(), role: 'Teammate' }));

            if (allRegs.length > 0) {
                myRegsContainer.style.display = "block";

                // Flatten events: Create a separate entry for each event in the registration
                const flattenedRegs = [];
                allRegs.forEach(reg => {
                    reg.events.forEach(eventName => {
                        flattenedRegs.push({
                            ...reg,
                            singleEvent: eventName
                        });
                    });
                });

                list.innerHTML = flattenedRegs.map(reg => `
                    <div class="registered-event-item">
                        <div class="reg-main-info">
                            <span class="event-name-tag">${reg.singleEvent}</span>
                        </div>
                        <div class="reg-details">
                            <div class="detail-line">
                                <strong>Member 1:</strong> ${reg.teamLeader.name} ${reg.academicDetails.rollNo ? `(${reg.academicDetails.rollNo})` : ''}
                            </div>
                            ${reg.teamMate && reg.teamMate.name ? `
                            <div class="detail-line">
                                <strong>Member 2:</strong> ${reg.teamMate.name} ${reg.teamMate.rollNo ? `(${reg.teamMate.rollNo})` : ''}
                            </div>
                            ` : ''}
                        </div>
                    </div>
                `).join("");
            } else {
                myRegsContainer.style.display = "none";
            }
        } catch (error) {
            console.error("Error loading registrations:", error);
        }
    };

    // Open Modal Function
    window.openRegistration = (eventName) => {
        if (!currentUser) {
            window.location.href = "auth.html?mode=register";
            return;
        }

        modal.style.display = "block";
        document.body.style.overflow = "hidden"; // Disable scroll

        // Pre-fill user data
        regName.value = currentUser.displayName || "";
        regEmail.value = currentUser.email || "@psgtech.ac.in";

        // Reset teammate fields
        const teammateEmail = document.getElementById("teammateEmail");
        if (teammateEmail) teammateEmail.value = "@psgtech.ac.in";

        // Reset all checkboxes
        const checkboxes = document.querySelectorAll('input[name="eventCheck"]');
        checkboxes.forEach(cb => cb.checked = false);

        // Select the event if passed
        if (eventName) {
            const checkbox = Array.from(checkboxes).find(cb => cb.value.toLowerCase() === eventName.toLowerCase());
            if (checkbox) checkbox.checked = true;
        }
    };

    // Dynamic Year of Study based on Degree
    const regDegree = document.getElementById("regDegree");
    const regYear = document.getElementById("regYear");

    if (regDegree && regYear) {
        regDegree.addEventListener('change', () => {
            const degree = regDegree.value;
            regYear.innerHTML = '<option value="">Year of Study</option>';

            if (!degree) {
                regYear.disabled = true;
                return;
            }

            regYear.disabled = false;
            let years = [];
            let defaultYear = null;

            switch (degree) {
                case "B.E":
                    years = [2, 3];
                    break;
                case "B.E (Sandwich)":
                    years = [2, 3, 4];
                    break;
                case "M.Sc":
                    years = [1, 2, 3, 4];
                    defaultYear = "1";
                    break;
                case "B.Sc":
                    years = [2];
                    break;
                case "MCA":
                    years = [1];
                    defaultYear = "1";
                    break;
                case "M.E":
                    years = [1, 2];
                    break;
            }

            years.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year + (year === 1 ? "st Year" : year === 2 ? "nd Year" : year === 3 ? "rd Year" : "th Year");
                regYear.appendChild(option);
            });

            if (defaultYear) {
                regYear.value = defaultYear;
            }
        });
    }

    // Solo Event Logic (GameHolix)
    const teammateSection = document.getElementById("teammateSection");
    const eventCheckboxes = document.querySelectorAll('input[name="eventCheck"]');

    const updateTeammateVisibility = (e) => {
        const selectedCheckboxes = Array.from(document.querySelectorAll('input[name="eventCheck"]:checked'));
        const selectedValues = selectedCheckboxes.map(cb => cb.value);

        // Gameholix Solo Constraint
        if (selectedValues.includes("GameHolix") && selectedValues.length > 1) {
            alert("GameHolix is a SOLO event and cannot be combined with other events in a single registration.");
            if (e && e.target) e.target.checked = false;
            return;
        }

        const isGameholix = selectedValues.includes("GameHolix");

        if (teammateSection) {
            teammateSection.style.display = isGameholix ? "none" : "block";
            // Clear teammate fields if hidden
            if (isGameholix) {
                document.getElementById("teammateName").value = "";
                document.getElementById("teammateEmail").value = "@psgtech.ac.in";
                document.getElementById("teammateRollNo").value = "";
                document.getElementById("teammatePhone").value = "";
            }
        }
    };

    eventCheckboxes.forEach(cb => {
        cb.addEventListener('change', (e) => updateTeammateVisibility(e));
    });

    // Update visibility when opening modal
    const originalOpenRegistration = window.openRegistration;
    window.openRegistration = (eventName) => {
        originalOpenRegistration(eventName);
        updateTeammateVisibility();
    };

    // Close Modal
    if (closeModal) {
        closeModal.onclick = () => {
            modal.style.display = "none";
            document.body.style.overflow = "auto";
        };
    }

    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = "none";
            document.body.style.overflow = "auto";
        }
    };

    // Attach Click Listeners to "Register Now" buttons
    const registerBtns = document.querySelectorAll('.register-btn');
    registerBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            // Get event name from the card
            const card = e.target.closest('.event-card');
            const eventName = card.querySelector('.event-name').textContent.trim();
            window.openRegistration(eventName);
        });
    });

    // Handle Form Submission
    if (registrationForm) {
        registrationForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!currentUser) {
                alert("You must be logged in.");
                return;
            }

            const selectedEvents = Array.from(document.querySelectorAll('input[name="eventCheck"]:checked')).map(cb => cb.value);

            if (selectedEvents.length === 0) {
                alert("Please select at least one event.");
                return;
            }

            const teamLeaderEmail = document.getElementById("regEmail").value;
            const teammateEmail = document.getElementById("teammateEmail").value;

            // Check for existing registrations for any of the selected events
            const checkDuplicates = async () => {
                const registrationsRef = collection(db, "registrations");
                const duplicateEvents = [];

                for (const event of selectedEvents) {
                    // Query for registrations where the event is present and either leader or teammate email matches
                    const qLeader = query(registrationsRef,
                        where("events", "array-contains", event),
                        where("teamLeader.email", "==", teamLeaderEmail)
                    );
                    const qTeammate = query(registrationsRef,
                        where("events", "array-contains", event),
                        where("teamMate.email", "==", teamLeaderEmail)
                    );

                    const [snapLeader, snapTeammate] = await Promise.all([getDocs(qLeader), getDocs(qTeammate)]);

                    if (!snapLeader.empty || !snapTeammate.empty) {
                        duplicateEvents.push(`${event} (You are already registered)`);
                        continue;
                    }

                    if (teammateEmail) {
                        const qTLeader = query(registrationsRef,
                            where("events", "array-contains", event),
                            where("teamLeader.email", "==", teammateEmail)
                        );
                        const qTTeammate = query(registrationsRef,
                            where("events", "array-contains", event),
                            where("teamMate.email", "==", teammateEmail)
                        );
                        const [snapTLeader, snapTTeammate] = await Promise.all([getDocs(qTLeader), getDocs(qTTeammate)]);

                        if (!snapTLeader.empty || !snapTTeammate.empty) {
                            duplicateEvents.push(`${event} (Teammate ${teammateEmail} is already registered)`);
                        }
                    }
                }
                return duplicateEvents;
            };

            try {
                const duplicates = await checkDuplicates();
                if (duplicates.length > 0) {
                    alert("Duplicate Registration Detected:\n\n" + duplicates.join("\n") + "\n\nPlease remove these events to proceed.");
                    return;
                }

                const registrationData = {
                    userId: currentUser.uid,
                    events: selectedEvents,
                    academicDetails: {
                        rollNo: document.getElementById("regRollNo").value,
                        degree: document.getElementById("regDegree").value,
                        year: document.getElementById("regYear").value,
                        department: document.getElementById("regCourse").value
                    },
                    teamLeader: {
                        name: document.getElementById("regName").value,
                        email: document.getElementById("regEmail").value,
                        phone: document.getElementById("regPhone").value
                    },
                    teamMate: {
                        name: document.getElementById("teammateName").value,
                        email: document.getElementById("teammateEmail").value,
                        rollNo: document.getElementById("teammateRollNo").value,
                        phone: document.getElementById("teammatePhone").value
                    },
                    timestamp: serverTimestamp()
                };

                try {
                    // 1. First, save to MySQL via PHP to get Team IDs
                    const response = await fetch('php/register_mysql.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(registrationData),
                    });

                    const mysqlResult = await response.json();

                    if (!mysqlResult.success) {
                        throw new Error(mysqlResult.message || "MySQL storage failed");
                    }

                    const teamIds = mysqlResult.teamIds; // mapping of { "EventName": "TR1" }

                    // 2. Save each event as a separate document in Firestore
                    const registrationsRef = collection(db, "registrations");
                    const firestorePromises = [];

                    for (const eventName of selectedEvents) {
                        const eventSpecificData = {
                            ...registrationData,
                            events: [eventName], // Keep as array with single event for query compatibility
                            event_name: eventName,
                            team_id: teamIds[eventName] || "N/A",
                            timestamp: serverTimestamp()
                        };
                        firestorePromises.push(addDoc(registrationsRef, eventSpecificData));
                    }

                    await Promise.all(firestorePromises);

                    // 3. Initialize Coin System in Firestore (users collection)
                    // This ensures users can see their team codes and 0 coins immediately
                    const usersRef = collection(db, "users");
                    const { setDoc, doc } = await import("https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js");

                    const coinPromises = [];
                    // For each event they registered for, we create/update their user coin entry?
                    // The requirement says "unique user id and their teamcode".
                    // If they stay 0 coins, it won't show on leaderboard anyway.
                    // But they need to see it on their dashboard.

                    for (const eventName of selectedEvents) {
                        const teamId = teamIds[eventName];
                        if (!teamId) continue;

                        // Entry for Leader
                        // We use a safe ID: leader_email_teamId
                        const leaderDocId = `leader_${registrationData.teamLeader.email}_${teamId}`.replace(/[@.]/g, '_');
                        coinPromises.push(setDoc(doc(db, "users", leaderDocId), {
                            user_id: currentUser.uid,
                            email: registrationData.teamLeader.email,
                            name: registrationData.teamLeader.name,
                            team_code: teamId,
                            coins: 0,
                            role: 'leader',
                            last_updated: serverTimestamp()
                        }, { merge: true }));

                        // Entry for Teammate
                        if (registrationData.teamMate && registrationData.teamMate.email) {
                            const teammateDocId = `mate_${registrationData.teamMate.email}_${teamId}`.replace(/[@.]/g, '_');
                            coinPromises.push(setDoc(doc(db, "users", teammateDocId), {
                                user_id: null, // To be claimed on login
                                email: registrationData.teamMate.email,
                                name: registrationData.teamMate.name,
                                team_code: teamId,
                                coins: 0,
                                role: 'teammate',
                                last_updated: serverTimestamp()
                            }, { merge: true }));
                        }
                    }
                    await Promise.all(coinPromises);

                    alert("Registration Successful for: " + selectedEvents.join(", ") + "! See you at the events.");
                    loadMyRegistrations();
                    modal.style.display = "none";
                    document.body.style.overflow = "auto";
                    registrationForm.reset();
                    regYear.innerHTML = '<option value="">Year of Study</option>';
                    regYear.disabled = true;
                } catch (error) {
                    console.error("Registration error: ", error);
                    alert("Error registering: " + error.message);
                }
            } catch (error) {
                console.error("Duplicate check or registration error: ", error);
                alert("An error occurred during registration. Please try again.");
            }
        });
    }
});
