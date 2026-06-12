export default function useFormatter() {
    const currency = (value) => {
        if (value === null || value === undefined) return "-";

        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(value);
    };

    return {
        currency,
    };
}
