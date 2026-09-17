const { Server } = require("socket.io");

/**
 * D1 · Notificación por sala (10 pts, bonus)
 *
 * Cada cliente se une a una sala identificada por su clienteId, y el
 * evento "pago:registrado" solo se envía a los sockets de esa sala
 * (no a todos los clientes conectados).
 */

const io = new Server(3000, {
  cors: { origin: "*" }, // ajustar en producción a los orígenes permitidos
});

io.on("connection", (socket) => {
  // El cliente, al conectarse, indica a qué clienteId pertenece y se
  // une a una sala con ese nombre.
  socket.on("join", (clienteId) => {
    socket.join(`cliente:${clienteId}`);
  });

  socket.on("disconnect", () => {
    // Socket.IO limpia automáticamente al socket de sus salas al
    // desconectarse, no se necesita hacer nada manual aquí.
  });
});

/**
 * Cuando se registra un pago (ej. desde el endpoint HTTP que crea el
 * pago, usando A2_registrar_pago.php del lado del backend PHP, o desde
 * cualquier otro servicio que dispare este evento), se emite solo a la
 * sala del cliente correspondiente:
 */
function notificarPagoRegistrado(clienteId, pago) {
  io.to(`cliente:${clienteId}`).emit("pago:registrado", pago);
}

module.exports = { io, notificarPagoRegistrado };

/**
 * Lado del cliente (referencia, no forma parte de lo pedido):
 *
 * const socket = io("http://localhost:3000");
 * socket.emit("join", clienteId);
 * socket.on("pago:registrado", (pago) => { ... actualizar UI ... });
 *
 * Nota para la entrevista: uso io.to(sala).emit(...) en vez de
 * socket.broadcast.emit(...) o io.emit(...) porque estos últimos
 * mandarían el evento a TODOS los sockets conectados (o a todos menos
 * el emisor), rompiendo el aislamiento por cliente que pide el ejercicio.
 * El prefijo "cliente:" en el nombre de sala es solo higiene para evitar
 * colisiones si en el futuro se agregan salas con otro propósito
 * (ej. "admin:" para sockets de panel interno).
 */
