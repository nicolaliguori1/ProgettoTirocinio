window.addEventListener("DOMContentLoaded", () => {
    const latElem = document.querySelectorAll(".box")[2].children[1];
    const lonElem = document.querySelectorAll(".box")[3].children[1];
    const statoElem = document.getElementById("stato-faro");
  
    const location = "Port of Southampton";
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}`;
  
    let lastSignalTime = null;
  
    function aggiornaCoordinateEDati() {
      fetch(url)
        .then((response) => {
          if (!response.ok) throw new Error("Errore nella richiesta");
          return response.json();
        })
        .then((data) => {
          if (data.length === 0) {
            latElem.textContent = "Coordinate non trovate";
            lonElem.textContent = "Coordinate non trovate";
            return;
          }
  
          const { lat, lon } = data[0];
          latElem.textContent = `${parseFloat(lat).toFixed(5)}° ${lat >= 0 ? "N" : "S"}`;
          lonElem.textContent = `${Math.abs(parseFloat(lon)).toFixed(5)}° ${lon >= 0 ? "E" : "W"}`;
  
          // Ricevuto segnale → aggiorna stato
          lastSignalTime = Date.now();
          statoElem.textContent = "Attivo";
          statoElem.style.color = "green";
        })
        .catch((error) => {
          console.error("Errore nel fetch:", error);
          latElem.textContent = "Errore rete";
          lonElem.textContent = "Errore rete";
        });
    }
  
    function controllaStatoFaro() {
      if (!lastSignalTime) return;
  
      const diff = Date.now() - lastSignalTime;
      if (diff > 2 * 60 * 1000) {
        statoElem.textContent = "Inattivo";
        statoElem.style.color = "red";
      }
    }
  
    // Primo fetch e avvio cicli
    aggiornaCoordinateEDati();
  
    // Fetch ogni 2 minuti = ricezione "segnale"
    setInterval(aggiornaCoordinateEDati, 2 * 60 * 1000);
  
    // Controlla ogni 10 secondi se è passato troppo tempo
    setInterval(controllaStatoFaro, 10000);
  });
  
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('../sw.js')
      .then(() => console.log('Service Worker registrato'))
      .catch(err => console.error('Service Worker fallito:', err));
  }
  