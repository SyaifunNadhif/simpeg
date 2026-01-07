document.addEventListener('DOMContentLoaded', function() {
    const elProduct = document.getElementById('product_type');
    const elInterest = document.getElementById('loan_interest');
    
    const elPlafonInput = document.getElementById('input-rupiah');
    const elPlafonInfo = document.getElementById('error-plafon'); 
    
    const elTenorInput = document.getElementById('input-tenor');
    const elTenorInfo = document.getElementById('error-tenor');

    const btnHitung = document.getElementById('btnHitung');
    let currentRate = 0; // Menyimpan bunga yang aktif saat ini

    // Helper: Format Rupiah Label
    function toLabel(num) {
        return "Rp " + new Intl.NumberFormat('id-ID').format(num);
    }

    // 1. Format Rupiah Input
    elPlafonInput.addEventListener('input', function(e) {
        this.value = formatRupiah(this.value);
        checkScheme(); // Cek bunga setiap ketik
    });

    elTenorInput.addEventListener('input', function(e) {
        checkScheme(); // Cek bunga setiap ganti tenor
    });

    // 2. Logic Ganti Produk
    elProduct.addEventListener('change', function(){
        const code = this.value;
        const prod = CONFIG.products[code];

        // Reset
        elPlafonInput.value = ''; elTenorInput.value = '';
        elInterest.value = ''; currentRate = 0;
        elPlafonInfo.style.display = 'none'; elTenorInfo.style.display = 'none';

        if(prod) {
            elPlafonInput.disabled = false; 
            elTenorInput.disabled = false;
            
            // Set batas input HTML (Batas Terluar)
            elTenorInput.min = prod.abs_min_tenor; 
            elTenorInput.max = prod.abs_max_tenor;
            elPlafonInput.placeholder = `Min: ${toLabel(prod.abs_min_plafon)}`;
            
            elInterest.placeholder = "Otomatis sesuai tenor...";
            btnHitung.style.display = 'inline-block';
        } else {
            elPlafonInput.disabled = true; 
            elTenorInput.disabled = true;
            btnHitung.style.display = 'none';
        }
    });

    // 3. FUNGSI PENCARI BUNGA (SCHEME CHECKER)
    function checkScheme() {
        const code = elProduct.value;
        if(!code) return;

        const prod = CONFIG.products[code];
        const plafon = cleanNumber(elPlafonInput.value);
        const tenor = parseInt(elTenorInput.value) || 0;

        // Reset Error dulu
        elPlafonInfo.style.display = 'none';
        elTenorInfo.style.display = 'none';
        elInterest.value = '';
        currentRate = 0;

        // Validasi Dasar (Batas Mutlak)
        if(plafon > 0 && (plafon < prod.abs_min_plafon || plafon > prod.abs_max_plafon)) {
            elPlafonInfo.innerText = `⚠️ Range Nominal: ${toLabel(prod.abs_min_plafon)} - ${toLabel(prod.abs_max_plafon)}`;
            elPlafonInfo.style.display = 'block';
            return;
        }
        if(tenor > 0 && (tenor < prod.abs_min_tenor || tenor > prod.abs_max_tenor)) {
            elTenorInfo.innerText = `⚠️ Range Tenor: ${prod.abs_min_tenor} - ${prod.abs_max_tenor} Bulan`;
            elTenorInfo.style.display = 'block';
            return;
        }

        // Cari Skema yang Cocok
        // Loop semua aturan di schemes, cari yang pas dengan Plafon & Tenor user
        let foundScheme = null;
        if(plafon > 0 && tenor > 0) {
            foundScheme = prod.schemes.find(s => 
                plafon >= s.min_p && plafon <= s.max_p &&
                tenor >= s.min_t && tenor <= s.max_t
            );
        }

        if(foundScheme) {
            // KETEMU!
            currentRate = foundScheme.rate;
            elInterest.value = `${currentRate}% (Flat)`;
            elInterest.style.backgroundColor = "#d1fae5"; // Hijau muda (valid)
        } else if (plafon > 0 && tenor > 0) {
            // Tidak ketemu (Mungkin kombinasi nominal & tenor tidak ada di SK)
            // Contoh: KMB (Nominal kecil tapi minta tenor 60 bulan -> Gak boleh)
            elInterest.value = "Kombinasi Tidak Tersedia";
            elInterest.style.backgroundColor = "#fee2e2"; // Merah muda (invalid)
        }
    }

    // 4. Logic Tombol Hitung
    btnHitung.addEventListener('click', function(){
        const plafon = cleanNumber(elPlafonInput.value);
        const tenor = parseInt(elTenorInput.value) || 0;

        if(currentRate === 0) {
            UI.showToast("Pastikan Nominal & Tenor sesuai aturan produk (Cek kolom Bunga)!", "error");
            return;
        }

        // Rumus Flat
        const totalBungaRp = plafon * (currentRate / 100) * (tenor / 12);
        const angsuranPerBulan = (plafon + totalBungaRp) / tenor;

        // Isi Modal
        document.getElementById('sim-pokok').innerText = toLabel(plafon);
        document.getElementById('sim-bunga').innerText = toLabel(totalBungaRp);
        document.getElementById('sim-cicilan').innerText = toLabel(angsuranPerBulan);
        // Tambahan info persentase di modal (opsional)
        // document.getElementById('sim-persen-label').innerText = currentRate + "%"; 

        // Buka Modal
        document.getElementById('modalSimulasi').classList.add('show');
    });
});