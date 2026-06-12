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
    if (!content) return [];
    const records: string[][] = [];
    let curField = '';
    let curRecord: string[] = [];
    let inQuotes = false;

    for (let i = 0; i < content.length; i++) {
        const ch = content[i];

        if (ch === '"') {
            if (inQuotes && content[i + 1] === '"') {
                curField += '"';
                i++;
            } else {
                inQuotes = !inQuotes;
            }
            continue;
        }

        if (ch === ',' && !inQuotes) {
            curRecord.push(curField);
            curField = '';
            continue;
        }

        // handle \r\n and \n as record separators, but only when not inside quotes
        if ((ch === '\n' || ch === '\r') && !inQuotes) {
            // consume optional \n after \r
            if (ch === '\r' && content[i + 1] === '\n') i++;
            curRecord.push(curField);
            records.push(curRecord);
            curRecord = [];
            curField = '';
            continue;
        }

        curField += ch;
    }

    // push any trailing field/record
    if (curField !== '' || curRecord.length > 0) {
        curRecord.push(curField);
        records.push(curRecord);
    }

    if (records.length === 0) return [];

    const headers = records[0].map((h) => String(h).trim());
    const rows: Row[] = [];
    for (let r = 1; r < records.length; r++) {
        const rec = records[r];
        const row: Row = {};
        for (let j = 0; j < headers.length; j++) {
            // preserve inner newlines, normalize CRs
            row[headers[j]] = rec[j] === undefined ? '' : String(rec[j]).replace(/\r/g, '').trim();
        }
        rows.push(row);
    }
    return rows;
}
