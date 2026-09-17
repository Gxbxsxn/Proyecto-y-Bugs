/**
 * D1 (real) · Servidor de notificaciones en tiempo real.
 *
 * - El frontend se conecta por WebSocket y hace socket.emit("join", clienteId)
 *   para unirse a la sala de ese cliente (igual que D-nodejs/D1_socketio_salas.js).
 * - El backend PHP, cuando registra un pago, llama a POST /notify aquí.
 * - Este servidor emite el evento SOLO a la sala del cliente correspondiente,
 *   no a todos los sockets conectados — así, si dos personas tienen abierta
 *   la ficha de clientes distintos, cada una solo se entera de lo suyo.
 *
 * Arrancar con:  npm install && npm start   (puerto 4000)
 */

const express = require("express");
const cors = require("cors");
const http = require("http");
const { Server } = require("socket.io");

const NOTIFY_SECRET = "oca-reto-tecnico-demo-secret-2026"; // debe coincidir con NotificadorTiempoReal.php
const PORT = 4000;

const app = express();
app.use(cors());
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, {
  cors: { origin: "*" }, // abierto para la demo local; en producción restringir orígenes
});

io.on("connection", (socket) => {
  console.log(`[socket] conectado: ${socket.id}`);

  socket.on("join", (clienteId) => {
    const sala = `cliente:${clienteId}`;
    socket.join(sala);
    console.log(`[socket] ${socket.id} se unió a la sala ${sala}`);
  });

  socket.on("disconnect", () => {
    console.log(`[socket] desconectado: ${socket.id}`);
  });
});

// Endpoint que llama el backend PHP tras registrar un pago (A2 -> D1).
app.post("/notify", (req, res) => {
  if (req.headers["x-notify-secret"] !== NOTIFY_SECRET) {
    return res.status(401).json({ error: "No autorizado." });
  }

  const { clienteId, pago } = req.body;
  if (!clienteId || !pago) {
    return res.status(400).json({ error: "Falta clienteId o pago." });
  }

  const sala = `cliente:${clienteId}`;
  io.to(sala).emit("pago:registrado", pago);
  console.log(`[notify] pago:registrado -> sala ${sala}`, pago);

  res.json({ ok: true });
});

app.get("/health", (_req, res) => res.json({ ok: true, uptime: process.uptime() }));

server.listen(PORT, () => {
  console.log(`Servidor de tiempo real escuchando en http://localhost:${PORT}`);
});
