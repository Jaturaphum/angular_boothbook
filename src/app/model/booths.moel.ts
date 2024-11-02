// To parse this data:
//
//   import { Convert } from "./file";
//
//   const Booth = Convert.toBooth(json);

export interface Booths {
    booth_id:     number;
    booth_name:   string;
    booth_size:   string;
    booth_status: string;
    booth_price:  string;
    zone_id:      number;
}

// Converts JSON strings to/from your types
export class Convert {
    public static toBooths(json: string): Booths[] {
        return JSON.parse(json);
    }

    public static BoothToJson(value: Booths[]): string {
        return JSON.stringify(value);
    }
}
