import { generateNextPosition } from "./gps.js";

let history = [];

function updateHistory(position) {
  history.push(position);
  if (history.length > 10) history.shift(); // Mantieni solo ultimi 10
}

function getHistory() {
  return [...history]; // Copia immutabile
}

function getStats() {
  return {
    totalPoints: history.length,
    first: history[0] || null,
    last: history[history.length - 1] || null,
  };
}

// 🔽 Nuova funzione per inviare la posizione al backend
async function saveToServer(position) {
  try {
    await fetch('/api/position', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(position),
    });
  } catch (error) {
    console.error("Errore nel salvataggio della posizione:", error);
  }
}

function stepSimulation() {
  const newPos = generateNextPosition();
  updateHistory(newPos);
  saveToServer(newPos); // 🔽 Invio al backend
  return newPos;
}

export { stepSimulation, getHistory, getStats };
//commento