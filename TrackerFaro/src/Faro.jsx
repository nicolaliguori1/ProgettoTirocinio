import React, { useState, useEffect } from 'react';

const TrackerFaro = () => {
  const [faro, setFaro] = useState({
    nome: "Faro di Calshot",
    latitudine: 50.8158,
    longitudine: -1.3103
  });

  const [stato, setStato] = useState({
    acceso: true,
    ultimoAggiornamento: new Date()
  });

  useEffect(() => {
    const timer = setInterval(() => {
      setStato(prev => ({
        ...prev,
        ultimoAggiornamento: new Date()
      }));
    }, 1000);

    return () => clearInterval(timer);
  }, []);

  return (
    <div style={{
      minHeight: '100vh',
      background: 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
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
          🏮 Monitoring {faro.nome}
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
          Informazioni Faro
        </h2>

        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '20px' }}>
          <div style={{
            background: 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)',
            borderRadius: '15px',
            padding: '20px',
            color: 'white',
            textAlign: 'center'
          }}>
            <div style={{ fontSize: '14px', opacity: '0.8', marginBottom: '5px' }}>Nome</div>
            <div style={{ fontSize: '18px', fontWeight: '600' }}>{faro.nome}</div>
          </div>

          <div style={{
            background: 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)',
            borderRadius: '15px',
            padding: '20px',
            color: 'white',
            textAlign: 'center'
          }}>
            <div style={{ fontSize: '14px', opacity: '0.8', marginBottom: '5px' }}>Latitudine</div>
            <div style={{ fontSize: '18px', fontWeight: '600' }}>{faro.latitudine.toFixed(6)}</div>
          </div>

          <div style={{
            background: 'linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%)',
            borderRadius: '15px',
            padding: '20px',
            color: 'white',
            textAlign: 'center'
          }}>
            <div style={{ fontSize: '14px', opacity: '0.8', marginBottom: '5px' }}>Longitudine</div>
            <div style={{ fontSize: '18px', fontWeight: '600' }}>{faro.longitudine.toFixed(6)}</div>
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
          Stato Operativo
        </h3>

        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '20px' }}>
          <div style={{
            background: stato.acceso ? 'linear-gradient(135deg, #27ae60 0%, #2ecc71 100%)' : 'linear-gradient(135deg, #e74c3c 0%, #c0392b 100%)',
            borderRadius: '15px',
            padding: '40px',
            color: 'white',
            textAlign: 'center'
          }}>
            <div style={{ fontSize: '48px', marginBottom: '10px' }}>{stato.acceso ? '🟢' : '🔴'}</div>
            <div style={{ fontSize: '18px', fontWeight: '600', marginBottom: '5px' }}>
              {stato.acceso ? 'Operativo' : 'Spento'}
            </div>
            <div style={{ fontSize: '14px', opacity: '0.9' }}>Stato Attuale</div>
          </div>

          <div style={{
            background: 'linear-gradient(135deg, #3498db 0%, #2980b9 100%)',
            borderRadius: '15px',
            padding: '40px',
            color: 'white',
            textAlign: 'center'
          }}>
            <div style={{ fontSize: '24px', fontWeight: '600', marginBottom: '10px' }}>
              {stato.ultimoAggiornamento.toLocaleTimeString()}
            </div>
            <div style={{ fontSize: '16px', fontWeight: '500', marginBottom: '5px' }}>Ultimo Update</div>
            <div style={{ fontSize: '14px', opacity: '0.9' }}>
              {stato.ultimoAggiornamento.toLocaleDateString()}
            </div>
          </div>
        </div>
      </div>

      <style>{`
        @keyframes pulse {
          0% { transform: scale(1); }
          50% { transform: scale(1.05); }
          100% { transform: scale(1); }
        }
      `}</style>
    </div>
  );
};

export default TrackerFaro;