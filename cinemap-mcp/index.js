import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { z } from "zod";
import dotenv from "dotenv";
import { fileURLToPath } from "url";
import { dirname, join } from "path";

const __dirname = dirname(fileURLToPath(import.meta.url));
dotenv.config({ path: join(__dirname, ".env"), quiet: true });

const BASE_URL = process.env.CINEMAP_BASE_URL;
const EMAIL = process.env.CINEMAP_EMAIL;
const PASSWORD = process.env.CINEMAP_PASSWORD;

let jwtToken = process.env.CINEMAP_JWT_TOKEN;

async function login() {
    const res = await fetch(`${BASE_URL}/api/auth/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ email: EMAIL, password: PASSWORD }),
    });
    const data = await res.json();
    if (data.access_token) {
        jwtToken = data.access_token;
    }
}

async function authedFetch(url) {
    let res = await fetch(url, { headers: { Authorization: `Bearer ${jwtToken}` } });
    if (res.status === 401 && EMAIL && PASSWORD) {
        await login();
        res = await fetch(url, { headers: { Authorization: `Bearer ${jwtToken}` } });
    }
    return res.json();
}

if (EMAIL && PASSWORD) {
    await login();
}

const server = new McpServer({ name: "cinemap", version: "1.0.0" });

server.tool("list_films", "Retourne la liste de tous les films CineMap", {}, async () => {
    const res = await fetch(`${BASE_URL}/api/films`);
    const data = await res.json();
    return { content: [{ type: "text", text: JSON.stringify(data, null, 2) }] };
});

server.tool(
    "get_localisations_for_film",
    "Retourne les emplacements de tournage d'un film CineMap",
    { film_id: z.number().describe("ID du film") },
    async ({ film_id }) => {
        const data = await authedFetch(`${BASE_URL}/api/films/${film_id}/localisations`);
        return { content: [{ type: "text", text: JSON.stringify(data, null, 2) }] };
    }
);

const transport = new StdioServerTransport();
await server.connect(transport);
