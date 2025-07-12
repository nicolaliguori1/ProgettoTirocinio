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

  const lastSection = storicoDiv.querySelector(".storico-box ul");
  if (lastSection) lastSection.remove(); // rimuove la lista vecchia
  storicoDiv.appendChild(list);
}

function tick() {
  const newPos = stepSimulation();
  renderPosition(newPos);
  renderStats();
  renderHistory();
}

// Avvia la simulazione ogni 2 secondi
setInterval(tick, 2000);