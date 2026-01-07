document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('loanForm');
    const toggleBtn = document.getElementById('btnToggleForm');
    const btnReset = document.getElementById('btnResetForm');
    const btnSearch = document.getElementById('btnSearch');
    const inputSearch = document.getElementById('searchCode');
    
    // Toggle Form
    toggleBtn.onclick = () => { 
        form.classList.toggle('hd-hidden'); 
        toggleBtn.innerText = form.classList.contains('hd-hidden') ? 'BUAT PENGAJUAN BARU' : 'TUTUP FORM';
    };

    // Validasi KTP
    document.getElementById('id_ktp').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, ''); 
        const error = document.getElementById('ktp-error');
        if (this.value.length > 0 && this.value.length !== 16) {
            error.style.display = 'block'; this.setCustomValidity('Invalid');
        } else {
            error.style.display = 'none'; this.setCustomValidity('');
        }
    });

    // Reset
    btnReset.onclick = () => { 
        form.reset(); 
        if(typeof window.resetWilayah === 'function') window.resetWilayah();
    };

    // ==========================================
    // 1. LOGIC SUBMIT (SIMPAN KE JSON LOCAL)
    // ==========================================
    form.onsubmit = async e => {
        e.preventDefault();
        if(!form.checkValidity()){ form.reportValidity(); return; }

        if(document.getElementById('error-plafon').style.display === 'block') {
            UI.showToast("Perbaiki nominal pinjaman!", "error"); return;
        }

        const btn = document.getElementById('btnSubmit');
        btn.disabled = true; btn.innerText = 'Memproses...';
        
        // Simulasi Loading
        setTimeout(() => {
            // A. Buat KODE UNIK (Tiket)
            const randomCode = Math.floor(1000 + Math.random() * 9000); // 4 digit acak
            const ticketId = "REG-" + randomCode;

            // B. Ambil Data Form
            const formData = new FormData(form);
            const dataJson = {
                ticket_id: ticketId,
                tanggal: new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }),
                nama: formData.get('nama_lengkap'),
                ktp: formData.get('no_ktp'),
                provinsi: formData.get('nama_provinsi'),
                kota: formData.get('nama_kota'),
                produk_code: formData.get('jenis_produk'), // kmb, joglo, dll
                produk_nama: document.getElementById('product_type').options[document.getElementById('product_type').selectedIndex].text,
                plafon: formData.get('besar_pinjaman'),
                tenor: formData.get('jangka_waktu') + " Bulan",
                status: "Menunggu Verifikasi Admin"
            };

            // C. SIMPAN KE LOCALSTORAGE (Database JSON Sementara)
            // Ambil data lama dulu, kalau kosong buat array baru
            let dbPengajuan = JSON.parse(localStorage.getItem('db_pengajuan_kredit') || "[]");
            dbPengajuan.push(dataJson);
            
            // Simpan balik ke LocalStorage
            localStorage.setItem('db_pengajuan_kredit', JSON.stringify(dbPengajuan));

            // D. Tampilkan Modal Sukses
            UI.showModalSuccess(ticketId);

            // E. Reset Form
            btn.disabled = false; btn.innerText = 'KIRIM PENGAJUAN';
            form.reset(); 
            if(typeof window.resetWilayah === 'function') window.resetWilayah();
            form.classList.add('hd-hidden');
            toggleBtn.innerText = 'BUAT PENGAJUAN BARU';

        }, 1500);
    };


    // ==========================================
    // 2. LOGIC CEK STATUS (SEARCH JSON)
    // ==========================================
    btnSearch.onclick = () => {
        const code = inputSearch.value.trim().toUpperCase();
        
        if(!code) {
            UI.showToast("Masukkan kode pengajuan dulu!", "error");
            return;
        }

        btnSearch.disabled = true; btnSearch.innerText = "...";

        setTimeout(() => {
            // A. Ambil Data dari LocalStorage
            const db = JSON.parse(localStorage.getItem('db_pengajuan_kredit') || "[]");
            
            // B. Cari Data yang Cocok
            const foundData = db.find(item => item.ticket_id === code);

            if(foundData) {
                // C. Isi Modal dengan Data JSON
                document.getElementById('st-ticket').innerText = foundData.ticket_id;
                document.getElementById('st-nama').innerText = foundData.nama;
                document.getElementById('st-produk').innerText = foundData.produk_nama;
                document.getElementById('st-plafon').innerText = "Rp " + foundData.plafon;
                document.getElementById('st-tenor').innerText = foundData.tenor;
                document.getElementById('st-tanggal').innerText = foundData.tanggal;

                // Tampilkan Modal
                document.getElementById('modalStatus').classList.add('show');
            } else {
                UI.showToast("Kode pengajuan tidak ditemukan!", "error");
            }

            btnSearch.disabled = false; btnSearch.innerText = "CEK STATUS";
        }, 800);
    };

});