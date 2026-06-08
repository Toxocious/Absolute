import * as fs from 'fs';
import * as path from 'path';

export type Row = Record<string, string>;

export const capitalize = (str: string, lower: boolean = false) =>
    (lower ? str.toLowerCase() : str).replace(/(?:^|\s|["'([{])+\S/g, (match) =>
        match.toUpperCase()
    );

export function ReadCsvFile(filename: string) {
    if (!filename) {
        throw new Error("No 'filename' was passed to ReadCsvFile().");
    }

    const CsvFile = fs.readFile(`csv/${filename}.csv`, function (error, data) {
        if (error) {
            throw new Error(`Error when reading '../../csv/${filename}.csv' || ${error.message}`);
        }

        console.log('Async file contents:\n---\n', data.toString());
    });
}

export function parseCSV(content: string): Row[] {
    const lines = content.replace(/\r\n/g, '\n').split('\n').filter(Boolean);
    if (lines.length === 0) return [];
    const headers = splitCSVLine(lines[0]);
    const rows: Row[] = [];
    for (let i = 1; i < lines.length; i++) {
        const cols = splitCSVLine(lines[i]);
        const row: Row = {};
        for (let j = 0; j < headers.length; j++) {
            row[headers[j]] = cols[j] ?? '';
        }
        rows.push(row);
    }
    return rows;
}

function splitCSVLine(line: string): string[] {
    const out: string[] = [];
    let cur = '';
    let inQuotes = false;
    for (let i = 0; i < line.length; i++) {
        const ch = line[i];
        if (ch === '"') {
            if (inQuotes && line[i + 1] === '"') {
                cur += '"';
                i++;
            } else {
                inQuotes = !inQuotes;
            }
            continue;
        }
        if (ch === ',' && !inQuotes) {
            out.push(cur);
            cur = '';
            continue;
        }
        cur += ch;
    }
    out.push(cur);
    return out.map((s) => s.trim());
}
