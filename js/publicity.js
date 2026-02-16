// Initialize Firebase (Compat)
const db = firebase.firestore();

const publicityForm = document.getElementById('publicityForm');
const submitBtn = document.getElementById('submitBtn');
const resultOverlay = document.getElementById('resultOverlay');
const idList = document.getElementById('idList');
const memberSelect = document.getElementById('publicityMember');
const teammateSection = document.getElementById('teammateSection');
const eventCheckboxes = document.querySelectorAll('input[name="eventCheck"]');

// 1. Fetch Publicity Members
async function loadMembers() {
    console.log("Loading members...");
    try {
        const res = await fetch('php/api_publicity_members.php?t=' + Date.now());
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);

        const members = await res.json();
        console.log("Members loaded:", members);

        if (members.length === 0) {
            const opt = document.createElement('option');
            opt.text = "No members found";
            memberSelect.add(opt);
        }

        members.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.name;
            opt.textContent = m.name;
            memberSelect.appendChild(opt);
        });
    } catch (e) {
        console.error("Failed to load members", e);
        // Fallback or alert
        const opt = document.createElement('option');
        opt.text = "Error loading members: " + e.message;
        memberSelect.add(opt);
        alert("Could not load Publicity Members list. Please check internet connection or database.");
    }
}
loadMembers();

// 2. Teammate Field Toggle Logic
function updateTeammateVisibility() {
    const selectedEvents = Array.from(document.querySelectorAll('input[name="eventCheck"]:checked')).map(cb => cb.value);

    // Check if any selected event requires 2 members (All except GameHolix)
    const requiresTeammate = selectedEvents.some(e => e !== "GameHolix");

    if (requiresTeammate) {
        teammateSection.style.display = 'block';
        // Set required attributes
        document.getElementById('tmName').setAttribute('required', 'true');
        document.getElementById('tmPhone').setAttribute('required', 'true');
        document.getElementById('tmRollNo').setAttribute('required', 'true');
        document.getElementById('tmEmail').setAttribute('required', 'true');
    } else {
        // Only GameHolix or nothing selected
        if (selectedEvents.includes("GameHolix")) {
            // GameHolix is solo, but if user wants to add teammate optionally? 
            // Requirement says "all events except gameholix is for two member". Implies GameHolix is 1 member.
            // We can hide it or make it optional. Let's make it optional/hidden but strictly not required.
            teammateSection.style.display = 'none';
        } else {
            teammateSection.style.display = 'none';
        }

        document.getElementById('tmName').removeAttribute('required');
        document.getElementById('tmPhone').removeAttribute('required');
        document.getElementById('tmRollNo').removeAttribute('required');
        document.getElementById('tmEmail').removeAttribute('required');
    }
}

eventCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateTeammateVisibility);
});
// Initial call
updateTeammateVisibility();


publicityForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const selectedEvents = Array.from(document.querySelectorAll('input[name="eventCheck"]:checked')).map(cb => cb.value);

    if (selectedEvents.length === 0) {
        alert("Please select at least one event.");
        return;
    }

    if (memberSelect.value === "") {
        alert("Please select the Publicity Member.");
        memberSelect.focus();
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerText = "SAVING...";

    const registrationData = {
        userId: "DESK_" + Date.now(),
        events: selectedEvents,
        publicityMember: memberSelect.value,
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
            name: document.getElementById("tmName").value,
            email: document.getElementById("tmEmail").value,
            rollNo: document.getElementById("tmRollNo").value,
            phone: document.getElementById("tmPhone").value
        }
    };

    try {
        // 1. Save to MySQL first to get Team IDs
        const response = await fetch('php/register_mysql.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(registrationData)
        });

        const mysqlResult = await response.json();

        if (!mysqlResult.success) {
            throw new Error(mysqlResult.message || "MySQL storage failed");
        }

        const teamIds = mysqlResult.teamIds;

        // 2. Firebase Auth (Anonymous)
        const auth = firebase.auth();
        if (!auth.currentUser) {
            await auth.signInAnonymously();
        }

        // 3. Save each event to Firestore as individual record
        const registrationsRef = db.collection("registrations");
        const firestorePromises = [];

        for (const eventName of selectedEvents) {
            const eventSpecificData = {
                ...registrationData,
                events: [eventName],
                event_name: eventName,
                team_id: teamIds[eventName] || "N/A",
                timestamp: firebase.firestore.FieldValue.serverTimestamp()
            };
            firestorePromises.push(registrationsRef.add(eventSpecificData));
        }

        await Promise.all(firestorePromises);

        // Show Results
        idList.innerHTML = "";
        for (const [event, tid] of Object.entries(teamIds)) {
            idList.innerHTML += `
                <div style="margin-bottom: 15px; background: rgba(255,255,255,0.05); padding: 15px; border-radius: 12px; border-left: 4px solid #bc13fe;">
                    <div style="font-size: 0.8rem; color: #888; text-transform: uppercase;">${event}</div>
                    <div style="font-size: 1.8rem; font-family: 'Orbitron', sans-serif; color: #bc13fe; font-weight: 900;">${tid}</div>
                </div>
            `;
        }

        resultOverlay.style.display = 'flex';

    } catch (error) {
        console.error(error);
        alert("Registration failed: " + error.message);
        submitBtn.disabled = false;
        submitBtn.innerText = "CONFIRM REGISTRATION";
    }
});
