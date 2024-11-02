// To parse this data:
//
//   import { Convert } from "./file";
//
//   const Member = Convert.toMember(json);

export interface Member {
    member_id:    number;
    title:        string;
    first_name:   string;
    last_name:    string;
    phone_number: string;
    email:        string;
}

// Converts JSON strings to/from your types
export class Convert {
    public static toMember(json: string): Member[] {
        return JSON.parse(json);
    }

    public static MemberToJson(value: Member[]): string {
        return JSON.stringify(value);
    }
}
