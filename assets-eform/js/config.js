const CONFIG = {
    // URL API Wilayah (Tetap)
    baseUrlWilayah: "https://www.emsifa.com/api-wilayah-indonesia/api",




    // --- DATA CABANG (Edit disini jika ada penambahan) ---
    DATA_CABANG: [
        { code: "KP", name: "Kantor Pusat (Semarang)" },
        { code: "KC_BANYUMANIK", name: "Cabang Banyumanik" },
        { code: "KC_UNGARAN", name: "Cabang Ungaran" },
        { code: "KC_SALATIGA", name: "Cabang Salatiga" },
        { code: "KC_SOLO", name: "Cabang Solo" },
        { code: "KK_PEDURUNGAN", name: "Kantor Kas Pedurungan" },
        { code: "KK_NGALIYAN", name: "Kantor Kas Ngaliyan" },
        { code: "KK_AMBARAWA", name: "Kantor Kas Ambarawa" },
        { code: "KK_MRANGGEN", name: "Kantor Kas Mranggen" }
    ],

    // DATABASE PRODUK SESUAI SK (Excel)
    products: {
        "kmb": {
            name: "Kredit Mikro BKK (KMB)",
            // Batas Mutlak (Untuk validasi awal)
            abs_min_plafon: 1000000, 
            abs_max_plafon: 1000000000,
            abs_min_tenor: 1, 
            abs_max_tenor: 60,
            // Daftar Aturan Bunga (Schemes)
            schemes: [
                // Plafon 1jt - 50jt, Tenor 1 - 36 bln -> 9%
                { min_p: 1000000, max_p: 50000000, min_t: 1, max_t: 36, rate: 9 },
                // Plafon > 50jt - 1M, Tenor 36 - 60 bln -> 12%
                { min_p: 50000001, max_p: 1000000000, min_t: 36, max_t: 60, rate: 12 }
            ]
        },
        "joglo": {
            name: "Kredit BKK Joglo",
            abs_min_plafon: 1000000, 
            abs_max_plafon: 3000000000,
            abs_min_tenor: 1, 
            abs_max_tenor: 180,
            schemes: [
                // Tenor 1 - 120 bln -> 11%
                { min_p: 1000000, max_p: 3000000000, min_t: 1, max_t: 120, rate: 11 },
                // Tenor 121 - 180 bln -> 12%
                { min_p: 1000000, max_p: 3000000000, min_t: 121, max_t: 180, rate: 12 }
            ]
        },
        "sinden": {
            name: "Kredit BKK Sinden",
            abs_min_plafon: 1000000, 
            abs_max_plafon: 1000000000,
            abs_min_tenor: 1, 
            abs_max_tenor: 84,
            schemes: [
                { min_p: 1000000, max_p: 1000000000, min_t: 1, max_t: 36, rate: 12 },
                { min_p: 1000000, max_p: 1000000000, min_t: 37, max_t: 60, rate: 14 },
                { min_p: 1000000, max_p: 1000000000, min_t: 61, max_t: 84, rate: 15 }
            ]
        },
        "korporasi": {
            name: "Kredit BKK Korporasi",
            abs_min_plafon: 100000000, 
            abs_max_plafon: 10000000000,
            abs_min_tenor: 1, 
            abs_max_tenor: 60,
            schemes: [
                // 1-12 bulan: 15% (Mengambil baris pertama)
                { min_p: 100000000, max_p: 10000000000, min_t: 1, max_t: 12, rate: 15 },
                // 13-60 bulan: 20% (Flat adjustment sesuai request)
                { min_p: 100000000, max_p: 10000000000, min_t: 13, max_t: 60, rate: 20 }
            ]
        },
        "k3": {
            name: "Kredit Kolektif Karyawan (K3)",
            abs_min_plafon: 1000000, 
            abs_max_plafon: 200000000,
            abs_min_tenor: 1, 
            abs_max_tenor: 120,
            schemes: [
                { min_p: 1000000, max_p: 200000000, min_t: 1, max_t: 36, rate: 10 },
                { min_p: 1000000, max_p: 200000000, min_t: 37, max_t: 60, rate: 11 },
                { min_p: 1000000, max_p: 200000000, min_t: 61, max_t: 120, rate: 12 }
            ]
        }
    }
};

