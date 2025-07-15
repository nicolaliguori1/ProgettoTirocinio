// gps.js
let currentPosition = {
    lat: 41.9028,
    lon: 12.4964,
  };
  
  function generateNextPosition() {
    // Simula leggera variazione di coordinate
    const deltaLat = (Math.random() - 0.5) * 0.01;
    const deltaLon = (Math.random() - 0.5) * 0.01;
  
    currentPosition.lat += deltaLat;
    currentPosition.lon += deltaLon;
  
    return {
      lat: currentPosition.lat.toFixed(5),
      lon: currentPosition.lon.toFixed(5),
      timestamp: new Date().toISOString(),
    };
  }
  
  export { generateNextPosition };
  //commento