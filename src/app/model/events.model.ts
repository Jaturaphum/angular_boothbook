// To parse this data:
//
//   import { Convert } from "./file";
//
//   const events = Convert.toEvents(json);

export interface Events {
    event_id:         string;
    event_name:       string;
    event_start_date: string;
    event_end_date:   string;
}

// Converts JSON strings to/from your types
export class Convert {
    public static toEvents(json: string): Events[] {
        return JSON.parse(json);
    }

    public static eventsToJson(value: Events[]): string {
        return JSON.stringify(value);
    }
}
