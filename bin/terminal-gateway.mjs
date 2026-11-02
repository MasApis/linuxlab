#!/usr/bin/env node

/**
 * LinuxLab Internal WebSocket Terminal Gateway
 * 
 * Bertindak sebagai jembatan real-time antara xterm.js di browser dan container Docker terisolasi
 * tanpa pernah mengekspos Docker socket ke browser atau publik (SEC-TERM-01, SEC-TERM-07).
 */

import { WebSocketServer, WebSocket } from 'ws';
import { spawn } from 'child_process';
import http from 'http';
import url from 'url';
import { loadGatewaySecret } from './load-dotenv-secret.mjs';

const PORT = parseInt(process.env.TERMINAL_WS_PORT || '3001', 10);
const HOST = process.env.TERMINAL_WS_HOST || '0.0.0.0';
const APP_URL = process.env.APP_INTERNAL_URL || process.env.APP_URL || 'http://127.0.0.1:8000';
// Dibaca dari .env proyek otomatis bila env proses kosong
const GATEWAY_SECRET = loadGatewaySecret();
// Hanya karakter aman untuk nama/ID container Docker
const SAFE_CONTAINER_ID = /^[a-zA-Z0-9][a-zA-Z0-9_.-]+$/;

const server = http.createServer((req, res) => {
    // Health check endpoint untuk backend
    if (req.url === '/health') {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ status: 'ok', service: 'linuxlab-terminal-gateway' }));
        return;
    }
    res.writeHead(404);
    res.end();
});

const wss = new WebSocketServer({ noServer: true });

// Autentikasi koneksi WebSocket melalui backend Laravel (SEC-TERM-09)
server.on('upgrade', async (request, socket, head) => {
    const parsedUrl = url.parse(request.url || '', true);

    if (parsedUrl.pathname !== '/ws/terminal') {
        socket.write('HTTP/1.1 404 Not Found\r\n\r\n');
        socket.destroy();
        return;
    }

    const token = parsedUrl.query.token;

    if (!token || typeof token !== 'string') {
        socket.write('HTTP/1.1 401 Unauthorized\r\n\r\n');
        socket.destroy();
        return;
    }

    try {
        // Validasi token sesi ke backend Laravel internal (gunakan 127.0.0.1 untuk keandalan IPv4)
        const baseAppUrl = APP_URL.replace('localhost', '127.0.0.1');
        const verifyUrl = `${baseAppUrl}/internal/terminal/verify?token=${encodeURIComponent(token)}`;
        const headers = { 'Accept': 'application/json' };
        if (GATEWAY_SECRET) {
            headers['X-Gateway-Secret'] = GATEWAY_SECRET;
        }
        const response = await fetch(verifyUrl, { headers });


        if (!response.ok) {
            console.warn('[Gateway] Token sesi tidak valid (ditolak backend).');
            socket.write('HTTP/1.1 403 Forbidden\r\n\r\n');
            socket.destroy();
            return;
        }

        const sessionData = await response.json();

        if (!sessionData.valid || !sessionData.container_id) {
            socket.write('HTTP/1.1 403 Forbidden\r\n\r\n');
            socket.destroy();
            return;
        }

        // Tolak ID container yang tidak sesuai pola aman sebelum dipakai ke shell
        if (!SAFE_CONTAINER_ID.test(sessionData.container_id)) {
            console.warn('[Gateway] container_id tidak valid, koneksi ditolak.');
            socket.write('HTTP/1.1 403 Forbidden\r\n\r\n');
            socket.destroy();
            return;
        }

        // Token valid! Upgrade koneksi ke WebSocket
        wss.handleUpgrade(request, socket, head, (ws) => {
            wss.emit('connection', ws, request, sessionData);
        });
    } catch (err) {
        console.error('[Gateway] Kesalahan saat memverifikasi sesi ke backend:', err);
        socket.write('HTTP/1.1 500 Internal Server Error\r\n\r\n');
        socket.destroy();
    }
});

wss.on('connection', (ws, request, sessionData) => {
    const containerId = sessionData.container_id;
    const userId = sessionData.user_id;

    console.log(`[Gateway] Pengguna ${userId} terhubung ke container ${containerId}`);

    // Sambungkan stream ke container Docker menggunakan pseudo-terminal (script)
    // script -qec "docker exec -it ..." /dev/null mengalokasikan PTY nyata di Linux
    let termProcess = null;

    try {
        termProcess = spawn('script', [
            '-qec',
            `docker exec -it -e TERM=xterm-256color ${containerId} /bin/bash`,
            '/dev/null',
        ]);
    } catch (e) {
        // Fallback jika utilitas script tidak tersedia
        console.warn('[Gateway] Fallback ke docker exec langsung tanpa script PTY wrapper');
        termProcess = spawn('docker', [
            'exec',
            '-i',
            '-e', 'TERM=xterm-256color',
            containerId,
            '/bin/bash',
        ]);
    }

    // Alirkan output terminal container ke browser (xterm.js)
    termProcess.stdout.on('data', (data) => {
        if (ws.readyState === WebSocket.OPEN) {
            ws.send(data);
        }
    });

    termProcess.stderr.on('data', (data) => {
        if (ws.readyState === WebSocket.OPEN) {
            ws.send(data);
        }
    });

    termProcess.on('error', (err) => {
        console.error(`[Gateway] Error pada proses container ${containerId}:`, err);
        if (ws.readyState === WebSocket.OPEN) {
            ws.send(`\r\n\x1b[31m[Kesalahan Sistem] Gagal menjalankan shell terminal: ${err.message}\x1b[0m\r\n`);
            ws.close(1011, 'Terminal process error');
        }
    });

    termProcess.on('close', (code) => {
        console.log(`[Gateway] Sesi container ${containerId} ditutup (exit code: ${code})`);
        if (ws.readyState === WebSocket.OPEN) {
            ws.send('\r\n\x1b[33m[Sesi terminal ditutup]\x1b[0m\r\n');
            ws.close(1000, 'Session closed');
        }
    });

    // Terima keystroke dan event dari browser
    ws.on('message', (message) => {
        if (!termProcess || termProcess.killed) return;

        const str = message.toString();

        // Tangani sinyal JSON resize dari xterm fit addon
        if (str.startsWith('{"type":"resize"')) {
            try {
                const payload = JSON.parse(str);
                if (payload.cols && payload.rows) {
                    // Update ukuran jendela terminal di container
                    spawn('docker', [
                        'exec',
                        containerId,
                        'stty',
                        'cols', String(payload.cols),
                        'rows', String(payload.rows),
                    ]).on('error', () => {});
                }
            } catch (_) {}
            return;
        }

        // Tulis input pengguna langsung ke stdin terminal container
        try {
            termProcess.stdin.write(message);
        } catch (err) {
            console.error('[Gateway] Gagal menulis ke stdin container:', err);
        }
    });

    ws.on('close', () => {
        console.log(`[Gateway] Sambungan WebSocket pengguna ${userId} terputus`);
        if (termProcess && !termProcess.killed) {
            termProcess.kill('SIGHUP');
        }
    });

    ws.on('error', (err) => {
        console.error('[Gateway] WebSocket client error:', err);
        if (termProcess && !termProcess.killed) {
            termProcess.kill('SIGHUP');
        }
    });
});

server.listen(PORT, HOST, () => {
    console.log(`[LinuxLab Terminal Gateway] Berjalan di http://${HOST}:${PORT}`);
    console.log(`[LinuxLab Terminal Gateway] Endpoint WebSocket: ws://${HOST}:${PORT}/ws/terminal?token=...`);
});

process.on('SIGINT', () => {
    console.log('\n[Gateway] Mematikan terminal gateway...');
    server.close(() => process.exit(0));
});
