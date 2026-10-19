<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import { Terminal } from '@xterm/xterm';
import { FitAddon } from '@xterm/addon-fit';
import '@xterm/xterm/css/xterm.css';

interface Props {
    wsToken?: string | null;
    wsPort?: number;
    autoFocus?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    wsToken: null,
    wsPort: 3001,
    autoFocus: true,
});

const emit = defineEmits<{
    (e: 'connected'): void;
    (e: 'disconnected'): void;
    (e: 'connecting'): void;
    (e: 'error', message: string): void;
}>();

const terminalContainer = ref<HTMLDivElement | null>(null);
const isConnected = ref(false);
const isConnecting = ref(false);

let terminal: Terminal | null = null;
let fitAddon: FitAddon | null = null;
let socket: WebSocket | null = null;
let resizeObserver: ResizeObserver | null = null;

const initTerminal = () => {
    if (!terminalContainer.value) return;

    terminal = new Terminal({
        cursorBlink: true,
        cursorStyle: 'block',
        fontSize: 14,
        fontFamily: "'JetBrains Mono', 'Fira Code', 'Courier New', monospace",
        lineHeight: 1.25,
        theme: {
            background: '#060e20',
            foreground: '#dae2fd',
            cursor: '#4edea3',
            cursorAccent: '#060e20',
            selectionBackground: '#2d3449',
            selectionForeground: '#ffffff',
            black: '#0b1326',
            red: '#ef4444',
            green: '#10b981',
            yellow: '#f59e0b',
            blue: '#3b82f6',
            magenta: '#a855f7',
            cyan: '#06b6d4',
            white: '#f3f4f6',
            brightBlack: '#475569',
            brightRed: '#f87171',
            brightGreen: '#34d399',
            brightYellow: '#fbbf24',
            brightBlue: '#60a5fa',
            brightMagenta: '#c084fc',
            brightCyan: '#22d3ee',
            brightWhite: '#ffffff',
        },
        convertEol: true,
    });

    fitAddon = new FitAddon();
    terminal.loadAddon(fitAddon);

    terminal.open(terminalContainer.value);
    fitAddon.fit();

    terminal.onData((data) => {
        if (socket && socket.readyState === WebSocket.OPEN) {
            socket.send(data);
        }
    });

    resizeObserver = new ResizeObserver(() => {
        handleResize();
    });
    resizeObserver.observe(terminalContainer.value);

    if (props.autoFocus) {
        terminal.focus();
    }
};

const handleResize = () => {
    if (!terminal || !fitAddon || !terminalContainer.value) return;

    try {
        fitAddon.fit();
        if (socket && socket.readyState === WebSocket.OPEN) {
            socket.send(JSON.stringify({
                type: 'resize',
                cols: terminal.cols,
                rows: terminal.rows,
            }));
        }
    } catch (_) {
        // Abaikan jika kontainer terminal sedang tersembunyi
    }
};

const connectWebSocket = () => {
    if (!props.wsToken) {
        if (terminal) {
            terminal.clear();
            terminal.writeln('\x1b[33m[Menunggu sesi terminal dibuat...]\x1b[0m');
        }
        return;
    }

    if (socket) {
        socket.close();
        socket = null;
    }

    isConnecting.value = true;
    isConnected.value = false;
    emit('connecting');

    if (terminal) {
        terminal.writeln('\x1b[36m[Menghubungkan ke container terminal terisolasi...]\x1b[0m');
    }

    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    const host = window.location.hostname || '127.0.0.1';
    const wsUrl = `${protocol}//${host}:${props.wsPort}/ws/terminal?token=${encodeURIComponent(props.wsToken)}`;

    try {
        socket = new WebSocket(wsUrl);

        socket.onopen = () => {
            isConnecting.value = false;
            isConnected.value = true;
            emit('connected');

            if (terminal) {
                terminal.clear();
                handleResize();
                terminal.focus();
            }
        };

        socket.onmessage = (event) => {
            if (terminal) {
                if (typeof event.data === 'string') {
                    terminal.write(event.data);
                } else if (event.data instanceof Blob) {
                    event.data.text().then((text) => terminal?.write(text));
                }
            }
        };

        socket.onclose = (event) => {
            isConnecting.value = false;
            isConnected.value = false;
            emit('disconnected');

            if (terminal && event.code !== 1000) {
                terminal.writeln(`\r\n\x1b[33m[Koneksi terminal terputus (kode: ${event.code})]\x1b[0m\r\n`);
            }
        };

        socket.onerror = () => {
            isConnecting.value = false;
            isConnected.value = false;
            const errorMsg = 'Layanan terminal gateway belum aktif pada port ' + props.wsPort + '. Jalankan "npm run terminal-gateway" di terminal server.';
            emit('error', errorMsg);

            if (terminal) {
                terminal.writeln(`\r\n\x1b[31m[Gagal terhubung ke layanan terminal gateway pada port ${props.wsPort}]\x1b[0m`);
                terminal.writeln(`\x1b[33mSilakan jalankan di terminal: npm run terminal-gateway\x1b[0m\r\n`);
            }
        };
    } catch (err) {
        isConnecting.value = false;
        isConnected.value = false;
        emit('error', 'Tidak dapat menginisialisasi WebSocket.');
    }
};


const clear = () => {
    terminal?.clear();
};

const focus = () => {
    terminal?.focus();
};

watch(() => props.wsToken, (newToken) => {
    if (newToken) {
        connectWebSocket();
    } else {
        if (socket) {
            socket.close();
            socket = null;
        }
        isConnected.value = false;
        isConnecting.value = false;
    }
});

onMounted(() => {
    initTerminal();
    if (props.wsToken) {
        connectWebSocket();
    }
});

onBeforeUnmount(() => {
    if (resizeObserver) {
        resizeObserver.disconnect();
    }
    if (socket) {
        socket.close();
        socket = null;
    }
    if (terminal) {
        terminal.dispose();
        terminal = null;
    }
});

defineExpose({
    clear,
    focus,
    reconnect: connectWebSocket,
    isConnected,
    isConnecting,
});
</script>

<template>
    <div class="relative w-full h-full flex flex-col min-h-[380px] bg-[#060e20]">
        <!-- Terminal Canvas Container -->
        <div ref="terminalContainer" class="flex-1 w-full h-full p-2.5 overflow-hidden"></div>

        <!-- Connection overlay status spinner -->
        <div
            v-if="isConnecting"
            class="absolute inset-0 bg-[#060e20]/80 backdrop-blur-xs flex items-center justify-center gap-2 text-emerald-400 font-mono text-xs z-10"
        >
            <svg class="animate-spin h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Menghubungkan ke shell container...</span>
        </div>
    </div>
</template>

<style>
/* Kustomisasi scrollbar xterm agar sesuai tema gelap LinuxLab */
.xterm .xterm-viewport::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.xterm .xterm-viewport::-webkit-scrollbar-track {
    background: #060e20;
}
.xterm .xterm-viewport::-webkit-scrollbar-thumb {
    background: #2d3449;
    border-radius: 4px;
}
.xterm .xterm-viewport::-webkit-scrollbar-thumb:hover {
    background: #4edea3;
}
</style>
