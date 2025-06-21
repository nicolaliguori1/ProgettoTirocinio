import React, { useState, useEffect } from 'react';

const TrackerBarca = () => {
  const [barca, setBarca] = useState({
    nome: "Titanic",
    latitudine: 50.9039,
    longitudine: -1.4043
  });

  const [posizioni, setPosizioni] = useState([]);

  useEffect(() => {
    // Velocità in gradi lat/lng per aggiornamento
    const velocità = 0.0005; 
    // Direzione in radianti (ad esempio 45°)
    const direzione = Math.PI / 4; 

    const timer = setInterval(() => {
      setBarca(barcaPrecedente => {
        const nuovaLat = barcaPrecedente.latitudine + velocità * Math.cos(direzione);
        const nuovaLng = barcaPrecedente.longitudine + velocità * Math.sin(direzione);
        const ora = new Date();

        setPosizioni(vecchiePosizioni => [
          ...vecchiePosizioni,
          {
            lat: nuovaLat,
            lng: nuovaLng,
            tempo: ora.toLocaleTimeString()
          }
        ]);
  
        return {
          ...barcaPrecedente,
          latitudine: nuovaLat,
          longitudine: nuovaLng
        };
      });
    }, 3000);

    return () => clearInterval(timer);
}, []);

  return (
    <div style={{
      minHeight: '100vh',
      background: 'linear-gradient(135deg, #007fff 0%, #ffffff 100%)',
      fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
      padding: '20px'
    }}>

      <div style={{
        background: 'rgba(255, 255, 255, 0.15)',
        backdropFilter: 'blur(20px)',
        borderRadius: '20px',
        padding: '25px',
        marginBottom: '25px',
        border: '1px solid rgba(255, 255, 255, 0.2)',
        boxShadow: '0 20px 40px rgba(0, 0, 0, 0.1)'
      }}>
        <h1 style={{
          fontSize: '32px',
          fontWeight: '700',
          color: 'white',
          margin: '0',
          textAlign: 'center',
          letterSpacing: '-0.5px'
        }}>
        Tracker Titanic
        </h1>
      </div>

      <div style={{
        background: 'rgba(255, 255, 255, 0.95)',
        backdropFilter: 'blur(20px)',
        borderRadius: '20px',
        padding: '30px',
        marginBottom: '25px',
        border: '1px solid rgba(255, 255, 255, 0.3)',
        boxShadow: '0 20px 40px rgba(0, 0, 0, 0.1)'
      }}>
        <h2 style={{
          fontSize: '24px',
          fontWeight: '600',
          color: '#1d1d1f',
          margin: '0 0 20px 0',
          letterSpacing: '-0.3px'
        }}>
          Posizione Live
        </h2>

        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '20px' }}>
          <div style={{
            background: 'linear-gradient(135deg, #007fff 0%, #ffffff 100%)',
            borderRadius: '15px',
            padding: '20px',
            color: 'white',
            textAlign: 'center',
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)' // leggera ombra per profondità
          }}>
            <div style={{ fontSize: '14px', opacity: '0.8', marginBottom: '5px' }}>Nome</div>
            <div style={{ fontSize: '18px', fontWeight: '600' }}>{barca.nome}</div>
          </div>

          <div style={{
            background: 'linear-gradient(135deg, #007fff 0%, #ffffff 100%)',
            borderRadius: '15px',
            padding: '20px',
            color: 'white',
            textAlign: 'center',
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)' // leggera ombra per profondità
          }}>
            <div style={{ fontSize: '14px', opacity: '0.8', marginBottom: '5px' }}>Latitudine</div>
            <div style={{ fontSize: '18px', fontWeight: '600' }}>{barca.latitudine.toFixed(6)}</div>
          </div>

          <div style={{
                  background: 'linear-gradient(135deg, #007fff 0%, #ffffff 100%)',
                  borderRadius: '15px',
                  padding: '20px',
                  color: 'white',
                  textAlign: 'center',
                  boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)' // leggera ombra per profondità
          }}>
            <div style={{ fontSize: '14px', opacity: '0.8', marginBottom: '5px' }}>Longitudine</div>
            <div style={{ fontSize: '18px', fontWeight: '600' }}>{barca.longitudine.toFixed(6)}</div>
          </div>
        </div>
      </div>

      <div style={{
        background: 'rgba(255, 255, 255, 0.95)',
        backdropFilter: 'blur(20px)',
        borderRadius: '20px',
        padding: '30px',
        marginBottom: '25px',
        border: '1px solid rgba(255, 255, 255, 0.3)',
        boxShadow: '0 20px 40px rgba(0, 0, 0, 0.1)'
      }}>
        <h3 style={{
          fontSize: '20px',
          fontWeight: '600',
          color: '#1d1d1f',
          margin: '0 0 20px 0',
          letterSpacing: '-0.2px'
        }}>
          Statistiche di Viaggio
        </h3>

        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '15px' }}>
          <div style={{
                  background: 'linear-gradient(135deg, #007fff 0%, #ffffff 100%)',
                  borderRadius: '15px',
                  padding: '20px',
                  color: 'white',
                  textAlign: 'center',
                  boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)' // leggera ombra per profondità
          }}>
            <div style={{ fontSize: '32px', fontWeight: '700', marginBottom: '5px' }}>{posizioni.length}</div>
            <div style={{ fontSize: '14px', opacity: '0.9' }}>Punti Tracciati</div>
          </div>

          <div style={{
              background: 'linear-gradient(135deg, #007fff 0%, #ffffff 100%)',
              borderRadius: '15px',
              padding: '20px',
              color: 'white',
              textAlign: 'center',
              boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)' // leggera ombra per profondità
          }}>
            <div style={{ fontSize: '16px', fontWeight: '600', marginBottom: '5px' }}>
              {posizioni.length > 0 ? posizioni[0].tempo : '--:--'}
            </div>
            <div style={{ fontSize: '14px', opacity: '0.9' }}>Primo Punto</div>
          </div>

          <div style={{
            background: 'linear-gradient(135deg, #007fff 0%, #ffffff 100%)',
            borderRadius: '15px',
            padding: '20px',
            color: 'white',
            textAlign: 'center',
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)' // leggera ombra per profondità
          }}>
            <div style={{ fontSize: '16px', fontWeight: '600', marginBottom: '5px' }}>
              {posizioni.length > 0 ? posizioni[posizioni.length - 1].tempo : '--:--'}
            </div>
            <div style={{ fontSize: '14px', opacity: '0.9' }}>Ultimo Punto</div>
          </div>
        </div>
      </div>

      <div style={{
        background: 'rgba(255, 255, 255, 0.95)',
        backdropFilter: 'blur(20px)',
        borderRadius: '20px',
        padding: '30px',
        border: '1px solid rgba(255, 255, 255, 0.3)',
        boxShadow: '0 20px 40px rgba(0, 0, 0, 0.1)'
      }}>
        <h3 style={{
          fontSize: '20px',
          fontWeight: '600',
          color: '#1d1d1f',
          margin: '0 0 20px 0',
          letterSpacing: '-0.2px'
        }}>
          Storico Recente
        </h3>

        <div style={{ maxHeight: '300px', overflowY: 'auto' }}>
          {posizioni.slice(-10).reverse().map((pos, index) => (
            <div key={index} style={{
              background: index % 2 === 0 ? 'rgba(116, 185, 255, 0.1)' : 'transparent',
              padding: '15px 20px',
              borderRadius: '12px',
              marginBottom: '8px',
              display: 'grid',
              gridTemplateColumns: '1fr 2fr 2fr',
              gap: '20px',
              alignItems: 'center',
              transition: 'all 0.2s ease'
            }}>
              <div style={{
                fontWeight: '600',
                color: 'black',
                fontSize: '14px'
              }}>
                {pos.tempo}
              </div>
              <div style={{
                color: '#1d1d1f',
                fontSize: '14px',
                fontFamily: 'SF Mono, Monaco, monospace'
              }}>
                {pos.lat.toFixed(6)}
              </div>
              <div style={{
                color: '#1d1d1f',
                fontSize: '14px',
                fontFamily: 'SF Mono, Monaco, monospace'
              }}>
                {pos.lng.toFixed(6)}
              </div>
            </div>
          ))}
        </div>
      </div>

      <style>{`
        @keyframes pulse {
          0% { transform: scale(1); }
          50% { transform: scale(1.1); }
          100% { transform: scale(1); }
        }
      `}</style>
    </div>
  );
};

export default TrackerBarca;
