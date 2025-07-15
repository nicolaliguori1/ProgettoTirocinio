import { stepSimulation, getStats, getHistory } from "./sync.js";

const latDisplay = document.querySelectorAll(".box")[1].children[1];
const lonDisplay = document.querySelectorAll(".box")[2].children[1];
const statBoxes = document.querySelectorAll(".sezione")[1].querySelectorAll(".box");

function renderPosition(pos) {
  latDisplay.textContent = pos.lat;
  lonDisplay.textContent = pos.lon;
}

function renderStats() {
  const stats = getStats();
  statBoxes[0].children[0].textContent = stats.totalPoints;
  statBoxes[1].children[0].textContent = stats.first
    ? `${stats.first.lat}, ${stats.first.lon}`
    : "...";
  statBoxes[2].children[0].textContent = stats.last
    ? `${stats.last.lat}, ${stats.last.lon}`
    : "...";
}

function renderHistory() {
  const history = getHistory();
  const list = document.getElementById("storico-list");
  list.innerHTML = "";

  history.slice().reverse().forEach((p) => {
    const li = document.createElement("li");
    li.textContent = `[${p.timestamp}] ${p.lat}, ${p.lon}`;
    list.appendChild(li);
  });
}

// Funzione per inviare la posizione al backend
function sendPositionToServer(position) {
  fetch("http://localhost:3000/api/position", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(position),
  })
    .then((res) => {
      if (!res.ok) throw new Error("Errore nel salvataggio");
      return res.json();
    })
    .then((data) => {
      console.log("✅ Posizione salvata:", data);
    })
    .catch((err) => {
      console.error("❌ Errore fetch:", err);
    });
}

function tick() {
  const newPos = stepSimulation();

  // Prepara dati con timestamp
  const payload = {
    lat: newPos.lat,
    lon: newPos.lon,
    timestamp: newPos.timestamp || new Date().toISOString(),
  };

  sendPositionToServer(payload);

  renderPosition(newPos);
  renderStats();
  renderHistory();
}

// Avvia la simulazione ogni 2 secondi
setInterval(tick, 2000);
