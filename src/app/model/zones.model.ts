// To parse this data:
//
//   import { Convert } from "./file";
//
//   const Zone = Convert.toZone(json);

export interface Zones {
    zone_id:     number;
    zone_name:   string;
    zone_info:   string;
    booth_count: string;
    event_id:    number;
}

// Converts JSON strings to/from your types
export class Convert {
    public static toZones(json: string): Zones[] {
        return JSON.parse(json);
    }

    public static ZonesToJson(value: Zones[]): string {
        return JSON.stringify(value);
    }
}
