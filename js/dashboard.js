import { auth, db } from './firebase-config-module.js';
import { onAuthStateChanged } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";
import { collection, query, where, getDocs, orderBy, limit, doc, getDoc, setDoc, updateDoc } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";

document.addEventListener("DOMContentLoaded", () => {
    const eventsGrid = document.getElementById("eventsGrid");
    const coinCount = document.getElementById("coinCount");
    const leaderboardSection = document.getElementById("leaderboardSection");
    const leaderboardBody = document.getElementById("leaderboardBody");

    onAuthStateChanged(auth, async (user) => {
        if (user) {
            console.log("Dashboard user:", user.email);
            await claimParticipation(user); // Link MySQL/Firestore entries if needed
            await loadDashboardRegistrations(user);
            await loadUserCoins(user);
            await loadLeaderboard();
        }
    });

    // Link participation if it was created by email only (teammate flow)
    const claimParticipation = async (user) => {
        try {
            // Check Firestore for placeholder or email-based entry
            const q = query(collection(db, "users"), where("email", "==", user.email));
            const snap = await getDocs(q);

            if (!snap.empty) {
                const userDoc = snap.docs[0];
                if (!userDoc.data().user_id) {
                    await updateDoc(doc(db, "users", userDoc.id), {
                        user_id: user.uid,
                        name: user.displayName
                    });
                }
            }
        } catch (error) {
            console.warn("Claim sync non-critical failure:", error);
        }
    };

    const loadUserCoins = async (user) => {
        if (!coinCount) return;
        try {
            const res = await fetch(`php/get_user_data.php?email=${encodeURIComponent(user.email)}`);
            const result = await res.json();

            if (result.success) {
                const data = result.data;
                window.numericUserId = data.id;
                coinCount.textContent = data.coins || 0;

                // Update header User ID display
                const idDisplay = document.getElementById("userNumericId");
                if (idDisplay) {
                    idDisplay.textContent = `#${data.id}`;
                    idDisplay.style.fontSize = "1.4rem";
                    idDisplay.style.fontWeight = "900";
                    idDisplay.style.textShadow = "0 0 10px var(--logo-purple)";
                }
            }
        } catch (error) {
            console.error("Error loading coins from MySQL:", error);
        }
    };

    const loadLeaderboard = async () => {
        if (!leaderboardBody) return;
        try {
            const q = query(collection(db, "users"), where("coins", ">", 0), orderBy("coins", "desc"), limit(10));
            const snap = await getDocs(q);

            if (!snap.empty) {
                leaderboardSection.style.display = "block";
                leaderboardBody.innerHTML = snap.docs.map((doc, index) => {
                    const data = doc.data();
                    return `
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 15px; font-weight: bold; color: ${index < 3 ? 'gold' : 'white'}">#${index + 1}</td>
                            <td style="padding: 15px;">
                                ${data.name || 'Anonymous'}<br>
                                <small style="opacity: 0.5;">${data.roll_no || ''}</small>
                            </td>
                            <td style="padding: 15px; color: gold; font-weight: bold;">${data.coins} 🪙</td>
                        </tr>
                    `;
                }).join("");
            }
        } catch (error) {
            console.error("Error loading leaderboard:", error);
        }
    };

    const loadDashboardRegistrations = async (user) => {
        if (!eventsGrid) return;
        try {
            const registrationsRef = collection(db, "registrations");
            const qLeader = query(registrationsRef, where("teamLeader.email", "==", user.email));
            const qTeammate = query(registrationsRef, where("teamMate.email", "==", user.email));

            const [snapLeader, snapTeammate] = await Promise.all([getDocs(qLeader), getDocs(qTeammate)]);

            const allRegs = [];
            snapLeader.forEach(doc => allRegs.push({ id: doc.id, ...doc.data(), role: 'Leader' }));
            snapTeammate.forEach(doc => allRegs.push({ id: doc.id, ...doc.data(), role: 'Teammate' }));

            renderRegistrations(allRegs);
        } catch (error) {
            console.error("Error loading registrations:", error);
        }
    };

    const eventLogistics = {
        "ProZone": { venue: "Hall A", date: "Feb 15", time: "10:00 AM" },
        "Incognito": { venue: "Hall B", date: "Feb 15", time: "11:30 AM" },
        "Inveringo": { venue: "Hall C", date: "Feb 15", time: "01:00 PM" },
        "TechRush": { venue: "IoT Lab", date: "Feb 16", time: "09:30 AM" },
        "Swaptics": { venue: "Hall D", date: "Feb 16", time: "11:00 AM" },
        "Fusion Frames": { venue: "Coding Lab", date: "Feb 16", time: "02:00 PM" },
        "GameHolix": { venue: "e-Sports Arena", date: "Feb 17", time: "10:00 AM" },
        "Tech Arcade": { venue: "Main Quad", date: "Feb 17", time: "12:00 PM" }
    };

    const renderRegistrations = (regs) => {
        if (regs.length === 0) {
            eventsGrid.innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; opacity: 0.5;"></i>
                    <p>You haven't registered for any events yet.</p>
                    <a href="index.html#events" class="browse-link" style="color: var(--logo-purple);">Browse Events</a>
                </div>
            `;
            return;
        }

        // Consolidated view: each event registration is a card
        eventsGrid.innerHTML = regs.map(reg => {
            const eventName = reg.event_name || (Array.isArray(reg.events) ? reg.events[0] : 'Event');
            const log = eventLogistics[eventName] || { venue: "TBD", date: "TBD", time: "TBD" };

            return `
            <div class="event-card-dash">
                <div class="event-status" style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="bi bi-patch-check-fill"></i> Confirmed</span>
                    <div style="text-align: right;">
                        <span style="color: rgba(255,255,255,0.5); font-size: 0.7rem; display: block;">Team: ${reg.team_id || 'N/A'}</span>
                        <span style="color: var(--logo-purple); font-size: 0.7rem; display: block;">User ID: ${window.numericUserId || '...'}</span>
                    </div>
                </div>
                <h3>${eventName}</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; font-size: 0.85rem; opacity: 0.8;">
                    <div><i class="bi bi-geo-alt"></i> ${log.venue}</div>
                    <div><i class="bi bi-calendar3"></i> ${log.date}</div>
                    <div style="grid-column: span 2;"><i class="bi bi-clock"></i> ${log.time}</div>
                </div>
                <div class="reg-info" style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px;">
                    <p style="margin: 5px 0;"><strong>Member 1:</strong> ${reg.teamLeader.name}</p>
                    ${reg.teamMate && reg.teamMate.name ? `<p style="margin: 5px 0;"><strong>Member 2:</strong> ${reg.teamMate.name}</p>` : ''}
                </div>
            </div>
        `;
        }).join("");
    };
});

