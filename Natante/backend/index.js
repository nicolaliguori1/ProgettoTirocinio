import express from "express";
import cors from "cors";
import dotenv from "dotenv";
import pool from "./db.js";

dotenv.config();

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors()); // abilita CORS per tutte le origini (solo per sviluppo)
app.use(express.json());

// Endpoint per salvare posizione
app.post("/api/position", async (req, res) => {
  const { lat, lon, timestamp } = req.body;

  if (lat === undefined || lon === undefined || !timestamp) {
    return res.status(400).json({ error: "Dati mancanti o non validi" });
  }

  try {
    const result = await pool.query(
      "INSERT INTO positions (lat, lon, timestamp) VALUES ($1, $2, $3) RETURNING *",
      [lat, lon, timestamp]
    );
    return res.status(201).json({ message: "Posizione salvata", data: result.rows[0] });
  } catch (err) {
    console.error("Errore DB:", err);
    return res.status(500).json({ error: "Errore interno del server" });
  }
});

// Endpoint di test base
app.get("/", (req, res) => {
  res.send("Backend Titanic OK");
});

// Avvio server
app.listen(PORT, () => {
  console.log(`🚢 Server avviato su http://localhost:${PORT}`);
});
