export type Gender = 'any' | 'male' | 'female';

export type RoomStatus = 'available' | 'fully_occupied' | 'maintenance';

export type SortBy = 'beds_count' | 'available_beds' & keyof Pick<Room, 'eligible_gender' | 'name'>

export type Filters = {
    selected_date?: Date | string | null;
    search?: string | undefined;
    eligible_gender?: RoomStatus | null;
    sort_by?: SortBy | null;
    sort_order?: 'asc' | 'desc';
}

export type Room = {
    id: number;
    name: string;
    eligible_gender: Gender;
    bed_price_rate: number;
}

export type BedStatus = 'available' | 'reserved' | 'occupied' | 'maintenance';

export type Bed = {
    id: number | string;
    name: string;
    price: number;
    room_id: number;
}

export type BedWithRoom = Bed & {
    room: Room;
}

export type RoomWithBedCounts = Room & {
    beds?: Bed[] | null;
    beds_count?: number | null;
    available_beds?: number | null;
    occupied_beds?: number | null;
    under_maintenance_beds?: number | null;
};

export type RoomWithBed = Room & {
    beds: Bed[]
}
