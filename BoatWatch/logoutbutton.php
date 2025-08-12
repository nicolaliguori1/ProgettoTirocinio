<style>
.logout-button {
    padding: 8px 16px; /* più piccolo */
    font-size: 0.9em;  /* testo leggermente ridotto */
    background: #ff4d4d; /* rosso acceso */
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
    width: 100px; /* ridotta larghezza */
    margin-top: 20px; /* ↑ distanzia il bottone dagli altri */
}

.logout-button:hover {
    background: #cc0000; /* rosso più scuro al passaggio */
}

</style>

<button onclick="location.href='../logout.php'" class="logout-button">
    Logout
</button>