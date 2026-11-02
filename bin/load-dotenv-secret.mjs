import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

/**
 * Ambil TERMINAL_GATEWAY_SECRET tanpa dependensi tambahan.
 * Urutan menang: env proses > file .env > string kosong.
 * Hanya key itu yang dibaca; nilainya tidak pernah di-log di mana pun.
 */
export function loadGatewaySecret(dotenvPath = '') {
    if (process.env.TERMINAL_GATEWAY_SECRET) {
        return process.env.TERMINAL_GATEWAY_SECRET;
    }

    const target =
        dotenvPath || path.join(path.dirname(fileURLToPath(import.meta.url)), '..', '.env');

    let content;
    try {
        content = fs.readFileSync(target, 'utf8');
    } catch {
        return '';
    }

    const match = content.match(/^(?:export\s+)?TERMINAL_GATEWAY_SECRET\s*=\s*(.*)$/m);
    if (!match) {
        return '';
    }

    let value = match[1].trim();
    if (value.startsWith('#') || value === '') {
        return '';
    }
    const first = value[0];
    const last = value[value.length - 1];
    if ((first === '"' && last === '"') || (first === "'" && last === "'")) {
        return value.slice(1, -1);
    }
    return value;
}
