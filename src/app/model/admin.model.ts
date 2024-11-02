// To parse this data:
//
//   import { Convert } from "./file";
//
//   const Admin = Convert.toAdmin(json);

export interface Admin {
    admin_id:           number;
    adminn_email:       string;
    admin_password:     string;
    admin_fname:        string;
    admin_title:        string;
    admin_lname:        string;
    admin_phone_number: string;
}

// Converts JSON strings to/from your types
export class Convert {
    public static toAdmin(json: string): Admin[] {
        return JSON.parse(json);
    }

    public static AdminToJson(value: Admin[]): string {
        return JSON.stringify(value);
    }
}
