
export const data = {
    breadcrumbs: [
        { label: 'Reservation Management', href: null }
    ],
    filterStatus: [
        {value: 'confirmed', label: 'Confirmed'},
        {value: 'checked_in', label: 'Checked In'},
        {value: 'checked_out', label: 'Checked Out'},
        {value: 'canceled', label: 'Canceled'},
    ],
    filterBalance: [
        {value: 'paid', label: 'Paid'},
        {value: 'has_balance', label: 'Has Balance'},
    ],
    sortBy: [
        { value: "reservation_code", label: "Reservation code" },
        { value: "first_name", label: "Book by" },
        { value: "check_in_date", label: "Checked in" },
        { value: "check_out_date", label: "Checked out" },
        { value: "total_billings", label: "Total billing" },
        { value: "status", label: "Status" },
    ],
    tableHeads: [
        "Reservation Code",
        "Book by",
        "Check in",
        "Check out",
        "Total Billings",
        "Remaining Balance",
        "Total Guests",
        "Guest Office",
        "Status",
        ""
    ],
};
