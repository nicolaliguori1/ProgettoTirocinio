import pg from "pg";
import dotenv from "dotenv";

dotenv.config();

const pool = new pg.Pool();

export default pool;
