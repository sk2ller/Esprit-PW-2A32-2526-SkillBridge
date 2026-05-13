const http = require('http');
const { Server } = require('socket.io');

const server = http.createServer();
const io = new Server(server, {
  cors: {
    origin: '*',
    methods: ['GET', 'POST']
  }
});

io.on('connection', (socket) => {
  socket.on('join-room', ({ room }) => {
    if (!room) {
      return;
    }

    socket.join(room);
  });

  socket.on('chat-message', (payload) => {
    if (!payload || !payload.room) {
      return;
    }

    socket.to(payload.room).emit('chat-message', payload);
  });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Socket.IO chat server running on http://localhost:${PORT}`);
});
