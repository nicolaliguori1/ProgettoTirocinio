import { generateNextPosition } from "./gps.js";

let history = [];

function updateHistory(position) {
  history.push(position);
  if (history.length > 10) history.shift(); // Mantieni solo gli ultimi 10 punti
}

function getHistory() {
  return [...history]; // restituisce una copia immutabile
}

function getStats() {
  return {
    totalPoints: history.length,
    first: history[0] || null,
    last: history[history.length - 1] || null,
  };
}

// Funzione per inviare la posizione al backend
async function saveToServer(position) {
  try {
    const response = await fetch("http://localhost:3000/api/position", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(position),
    });
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    console.log("✅ Posizione salvata:", data);
  } catch (error) {
    console.error("❌ Errore nel salvataggio della posizione:", error);
  }
}

function stepSimulation() {
  const newPos = generateNextPosition();

  // Assicurati che newPos abbia timestamp
  if (!newPos.timestamp) {
    newPos.timestamp = new Date().toISOString();
  }

  updateHistory(newPos);
  saveToServer(newPos); // invia al backend (in modo asincrono, non blocca)

  return newPos;
}

export { stepSimulation, getHistory, getStats };

