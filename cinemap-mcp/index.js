import { McpServer } from "@modelcontextprotocol/sdk/server/mcp.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { z } from "zod";
import dotenv from "dotenv";
dotenv.config();


const BASE_URL = "http://localhost:8000";
const JWT_TOKEN = process.env.CINEMAP_JWT_TOKEN; // token d'un user abonné

const server = new McpServer({ name: "cinemap", version: "1.0.0" });

// Outil 1 — liste tous les films
server.tool("list_films", "Retourne la liste de tous les films CineMap", {}, async () => {
    const res = await fetch(`${BASE_URL}/api/films`);
    const data = await res.json();
    return { content: [{ type: "text", text: JSON.stringify(data, null, 2) }] };
});

// Outil 2 — localisations d'un film
server.tool(
    "get_locations_for_film",
    "Retourne les emplacements de tournage d'un film CineMap",
    { film_id: z.number().describe("ID du film") },
    async ({ film_id }) => {
        const res = await fetch(`${BASE_URL}/api/films/${film_id}/locations`, {
            headers: { Authorization: `Bearer ${JWT_TOKEN}` },
        });
        const data = await res.json();
        return { content: [{ type: "text", text: JSON.stringify(data, null, 2) }] };
    }
);

const transport = new StdioServerTransport();
await server.connect(transport);