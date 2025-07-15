import express from "express";
import pool from "./db.js";
import dotenv from "dotenv";

dotenv.config();

const app = express();
const PORT = 3000;

app.use(express.json());

// ✅ Endpoint per salvare posizione
app.post("/api/position", async (req, res) => {
  const { lat, lon, timestamp } = req.body;

  if (!lat || !lon || !timestamp) {
    return res.status(400).json({ error: "Dati mancanti" });
  }

  try {
    const result = await pool.query(
      "INSERT INTO positions (lat, lon, timestamp) VALUES ($1, $2, $3) RETURNING *",
      [lat, lon, timestamp]
    );

    res.status(201).json({ message: "Posizione salvata", data: result.rows[0] });
  } catch (err) {
    console.error("Errore DB:", err);
    res.status(500).json({ error: "Errore server" });
  }
});

// 🟢 Test semplice
app.get("/", (req, res) => {
  res.send("Backend Titanic OK");
});

app.listen(PORT, () => {
  console.log(`🚢 Server avviato su http://localhost:${PORT}`);
});
/*commento */