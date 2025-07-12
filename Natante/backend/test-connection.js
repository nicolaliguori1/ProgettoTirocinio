import pool from "./db.js";

async function testConnection() {
  try {
    const res = await pool.query("SELECT NOW()");
    console.log("✅ Connessione al DB riuscita! Data/ora server:", res.rows[0].now);
  } catch (err) {
    console.error("❌ Errore di connessione al DB:", err);
  } finally {
    await pool.end();
  }
}

testConnection();
