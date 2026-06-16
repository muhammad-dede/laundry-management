export default function useStatusBadge() {
    const orderStatusBadge = (status) => {
        const statuses = {
            QUEUED: {
                label: "Antrian",
                class: "bg-slate-500 text-white hover:bg-slate-500",
            },
            PROCESS: {
                label: "Proses",
                class: "bg-blue-500 text-white hover:bg-blue-500",
            },
            READY: {
                label: "Siap Diambil",
                class: "bg-purple-500 text-white hover:bg-purple-500",
            },
            COMPLETED: {
                label: "Selesai",
                class: "bg-green-500 text-white hover:bg-green-500",
            },
        };

        return (
            statuses[status] ?? {
                label: status,
                class: "",
            }
        );
    };

    const paymentStatusBadge = (status) => {
        const statuses = {
            UNPAID: {
                label: "Belum Lunas",
                class: "bg-red-500 text-white hover:bg-red-500",
            },
            PAID: {
                label: "Lunas",
                class: "bg-green-500 text-white hover:bg-green-500",
            },
        };

        return (
            statuses[status] ?? {
                label: status,
                class: "",
            }
        );
    };

    return {
        orderStatusBadge,
        paymentStatusBadge,
    };
}
