export default function useFormatter() {
    const currency = (value) => {
        if (value === null || value === undefined) return "-";

        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(value);
    };

    const date = (value) => {
        if (!value) return "-";

        return new Intl.DateTimeFormat("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
        }).format(new Date(value));
    };

    const datetime = (value) => {
        if (!value) return "-";

        return new Intl.DateTimeFormat("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
        }).format(new Date(value));
    };

    const time = (value) => {
        if (!value) return "-";

        return new Intl.DateTimeFormat("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
        }).format(new Date(value));
    };

    return {
        currency,
        date,
        datetime,
        time,
    };
}
