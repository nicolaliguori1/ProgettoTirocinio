import React, { useState, useEffect } from 'react';

const TrackerBarca = () => {
  const [barca, setBarca] = useState({
    nome: "Barca Test",
    latitudine: 44.1015,
    longitudine: 9.8195
  });

  const [posizioni, setPosizioni] = useState([]);

  useEffect(() => {
    const timer = setInterval(() => {
      const nuovaLat = barca.latitudine + (Math.random() - 0.5) * 0.001;
      const nuovaLng = barca.longitudine + (Math.random() - 0.5) * 0.001;
      const ora = new Date();

      setBarca({
        ...barca,
        latitudine: nuovaLat,
        longitudine: nuovaLng
      });

      setPosizioni(vecchiePosizioni => [
        ...vecchiePosizioni,
        {
          lat: nuovaLat,
          lng: nuovaLng,
          tempo: ora.toLocaleTimeString()
        }
      ]);
    }, 3000);

    return () => clearInterval(timer);
  }, [barca]);

  return (
    <div style={{
      minHeight: '100vh',
      background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
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
          🚢 Tracker della Barca
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
            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            borderRadius: '15px',
            padding: '20px',
            color: 'white',
            textAlign: 'center'
          }}>
            <div style={{ fontSize: '14px', opacity: '0.8', marginBottom: '5px' }}>Nome</div>
            <div style={{ fontSize: '18px', fontWeight: '600' }}>{barca.nome}</div>
          </div>

          <div style={{
            background: 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
            borderRadius: '15px',
            padding: '20px',
            color: '#8b4513',
            textAlign: 'center'
          }}>
            <div style={{ fontSize: '14px', opacity: '0.8', marginBottom: '5px' }}>Latitudine</div>
            <div style={{ fontSize: '18px', fontWeight: '600' }}>{barca.latitudine.toFixed(6)}</div>
          </div>

          <div style={{
            background: 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
            borderRadius: '15px',
            padding: '20px',
            color: '#2c5aa0',
            textAlign: 'center'
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
          Mappa di Navigazione
        </h3>

        <div style={{
          background: 'linear-gradient(135deg, #74b9ff 0%, #0984e3 100%)',
          height: '350px',
          borderRadius: '15px',
          position: 'relative',
          overflow: 'hidden',
          boxShadow: '0 10px 25px rgba(0, 0, 0, 0.15)'
        }}>
          <div style={{
            position: 'absolute',
            top: '30px',
            left: '30px',
            background: 'rgba(0, 0, 0, 0.8)',
            color: 'white',
            padding: '12px 20px',
            borderRadius: '25px',
            fontSize: '14px',
            fontWeight: '600',
            backdropFilter: 'blur(10px)',
            border: '1px solid rgba(255, 255, 255, 0.2)'
          }}>
            ⚓ PORTO DI BOLANO
          </div>

          {posizioni.slice(-20).map((pos, index) => (
            <div key={index} style={{
              position: 'absolute',
              top: `${120 + (pos.lat - 44.1) * 15000}px`,
              left: `${120 + (pos.lng - 9.82) * 15000}px`,
              width: `${3 + index * 0.5}px`,
              height: `${3 + index * 0.5}px`,
              background: `rgba(255, 255, 255, ${0.3 + index * 0.035})`,
              borderRadius: '50%',
              transition: 'all 0.3s ease'
            }} />
          ))}

          <div style={{
            position: 'absolute',
            top: `${120 + (barca.latitudine - 44.1) * 15000}px`,
            left: `${120 + (barca.longitudine - 9.82) * 15000}px`,
            width: '24px',
            height: '24px',
            background: 'linear-gradient(135deg, #fd79a8 0%, #e84393 100%)',
            borderRadius: '50%',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '12px',
            boxShadow: '0 0 20px rgba(253, 121, 168, 0.8)',
            animation: 'pulse 2s infinite',
            border: '3px solid white'
          }}>
            🚢
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
            background: 'linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%)',
            borderRadius: '15px',
            padding: '25px',
            color: 'white',
            textAlign: 'center'
          }}>
            <div style={{ fontSize: '32px', fontWeight: '700', marginBottom: '5px' }}>{posizioni.length}</div>
            <div style={{ fontSize: '14px', opacity: '0.9' }}>Punti Tracciati</div>
          </div>

          <div style={{
            background: 'linear-gradient(135deg, #00cec9 0%, #55a3ff 100%)',
            borderRadius: '15px',
            padding: '25px',
            color: 'white',
            textAlign: 'center'
          }}>
            <div style={{ fontSize: '16px', fontWeight: '600', marginBottom: '5px' }}>
              {posizioni.length > 0 ? posizioni[0].tempo : '--:--'}
            </div>
            <div style={{ fontSize: '14px', opacity: '0.9' }}>Primo Punto</div>
          </div>

          <div style={{
            background: 'linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%)',
            borderRadius: '15px',
            padding: '25px',
            color: 'white',
            textAlign: 'center'
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
                color: '#667eea',
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
