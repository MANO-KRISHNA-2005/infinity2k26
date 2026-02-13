import { auth, db } from './firebase-config.js';
import { onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";
import { collection, query, where, getDocs } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";

document.addEventListener("DOMContentLoaded", () => {
    const eventsGrid = document.getElementById("eventsGrid");

    onAuthStateChanged(auth, async (user) => {
        if (user) {
            console.log("Fetching registrations for dashboard:", user.email);
            await loadDashboardRegistrations(user);
        }
    });

    const loadDashboardRegistrations = async (user) => {
        if (!eventsGrid) return;

        try {
            const registrationsRef = collection(db, "registrations");

            // Query for registrations where the user is either the leader or the teammate
            const qLeader = query(registrationsRef, where("teamLeader.email", "==", user.email));
            const qTeammate = query(registrationsRef, where("teamMate.email", "==", user.email));

            const [snapLeader, snapTeammate] = await Promise.all([
                getDocs(qLeader),
                getDocs(qTeammate)
            ]);

            const allRegs = [];
            snapLeader.forEach(doc => allRegs.push({ id: doc.id, ...doc.data(), role: 'Leader' }));
            snapTeammate.forEach(doc => allRegs.push({ id: doc.id, ...doc.data(), role: 'Teammate' }));

            renderRegistrations(allRegs);
        } catch (error) {
            console.error("Error loading dashboard registrations:", error);
            eventsGrid.innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-exclamation-triangle" style="font-size: 3rem; color: #ff4d4d;"></i>
                    <p>Error loading your registrations. Please try again later.</p>
                </div>
            `;
        }
    };

    const renderRegistrations = (regs) => {
        if (regs.length === 0) {
            eventsGrid.innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; opacity: 0.5;"></i>
                    <p>You haven't registered for any events yet.</p>
                    <a href="index.html#events" class="browse-link">Browse Events</a>
                </div>
            `;
            return;
        }

        // Flatten events: Create a separate entry for each event in the registration
        const flattenedRegs = [];
        regs.forEach(reg => {
            reg.events.forEach(eventName => {
                flattenedRegs.push({
                    ...reg,
                    singleEvent: eventName
                });
            });
        });

        eventsGrid.innerHTML = flattenedRegs.map(reg => `
            <div class="event-card-dash">
                <div class="event-status">Confirmed</div>
                <h3>${reg.singleEvent}</h3>
                <div class="reg-info">
                    <p><strong>Name:</strong> ${reg.teamLeader.name} ${reg.academicDetails.rollNo ? `(${reg.academicDetails.rollNo})` : ''}</p>
                    ${reg.teamMate && reg.teamMate.name ? `
                        <p><strong>Name:</strong> ${reg.teamMate.name} ${reg.teamMate.rollNo ? `(${reg.teamMate.rollNo})` : ''}</p>
                    ` : ''}
                </div>
                <div class="reg-date">
                    <small>Registered on: ${reg.timestamp ? new Date(reg.timestamp.toDate()).toLocaleDateString() : 'N/A'}</small>
                </div>
            </div>
        `).join("");
    };
});
