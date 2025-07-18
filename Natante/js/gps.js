// gps.js

let currentPosition = {
  lat: 41.9028,
  lon: 12.4964,
};

// Limiti del Mar Tirreno (approssimativi)
const LAT_MIN = 38.5;
const LAT_MAX = 42.5;
const LON_MIN = 8.0;
const LON_MAX = 12.5;

function generateNextPosition() {
  let deltaLat = (Math.random() - 0.5) * 0.01;
  let deltaLon = (Math.random() - 0.5) * 0.01;

  let newLat = currentPosition.lat + deltaLat;
  let newLon = currentPosition.lon + deltaLon;

  // Applichiamo vincoli: se esce dai limiti, inverti la direzione
  if (newLat < LAT_MIN || newLat > LAT_MAX) deltaLat *= -1;
  if (newLon < LON_MIN || newLon > LON_MAX) deltaLon *= -1;

  currentPosition.lat += deltaLat;
  currentPosition.lon += deltaLon;

  return {
    lat: currentPosition.lat.toFixed(5),
    lon: currentPosition.lon.toFixed(5),
    timestamp: new Date().toISOString(),
  };
}

export { generateNextPosition };
