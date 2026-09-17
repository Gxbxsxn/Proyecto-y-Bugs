const http = require('http');
const fs = require('fs');
const path = require('path');
const { Server } = require('socket.io');
const Database = require('better-sqlite3');

const PORT = 3001;
const appDir = __dirname;
const db = new Database(path.join(appDir, 'pagos.db'));

db.exec(`
  CREATE TABLE IF NOT EXISTS pagos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id TEXT NOT NULL,
    monto REAL NOT NULL,
    fecha TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
  );
`);

const server = http.createServer((req, res) => {
  const requestedPath = req.url === '/' ? '/demo_socketio.html' : req.url;
  const filePath = path.join(appDir, requestedPath);

  if (!filePath.startsWith(appDir)) {
    res.writeHead(403);
    res.end('Forbidden');
    return;
  }

  fs.readFile(filePath, (err, content) => {
    if (err) {
      res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
      res.end('Archivo no encontrado');
      return;
    }

    const ext = path.extname(filePath).toLowerCase();
    const mimeTypes = {
      '.html': 'text/html; charset=utf-8',
      '.js': 'application/javascript; charset=utf-8',
      '.css': 'text/css; charset=utf-8',
      '.json': 'application/json; charset=utf-8'
    };

    res.writeHead(200, { 'Content-Type': mimeTypes[ext] || 'text/plain; charset=utf-8' });
    res.end(content);
  });
});

const io = new Server(server, {
  cors: { origin: '*' }
});

io.on('connection', (socket) => {
  socket.on('join', (clienteId) => {
    socket.join(`cliente:${clienteId}`);
    socket.emit('status', { ok: true, clienteId });
  });

  socket.on('registrar_pago', ({ clienteId, monto }) => {
    if (!clienteId || !monto || Number(monto) <= 0) {
      socket.emit('error', { mensaje: 'Datos inválidos' });
      return;
    }

    const stmt = db.prepare(
      'INSERT INTO pagos (cliente_id, monto, fecha) VALUES (?, ?, CURRENT_TIMESTAMP)'
    );
    const result = stmt.run(String(clienteId), Number(monto));

    const pago = {
      id: result.lastInsertRowid,
      clienteId: String(clienteId),
      monto: Number(monto),
      fecha: new Date().toISOString()
    };

    io.to(`cliente:${clienteId}`).emit('pago:registrado', pago);
    socket.emit('pago:guardado', pago);
  });

  socket.on('disconnect', () => {
    // Socket.IO limpia automáticamente las salas.
  });
});

server.listen(PORT, () => {
  console.log(`Servidor Socket.IO listo en http://localhost:${PORT}`);
});
