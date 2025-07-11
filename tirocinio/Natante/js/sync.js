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

function stepSimulation() {
  const newPos = generateNextPosition();
  updateHistory(newPos);
  return newPos;
}

export { stepSimulation, getHistory, getStats };