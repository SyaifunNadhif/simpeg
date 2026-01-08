document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // 1. INISIALISASI VARIABEL GLOBAL
    // ==========================================
    const form = document.getElementById('loanForm');
    const toggleBtn = document.getElementById('btnToggleForm');
    const btnReset = document.getElementById('btnResetForm');
    const btnSearch = document.getElementById('btnSearch');
    const inputSearch = document.getElementById('searchCode');
    const selectCabang = document.getElementById('select-cabang');

    // ==========================================
    // 2. LOGIC DROPDOWN CABANG (Load dari Config)
    // ==========================================
    if (selectCabang) {
        // Cek apakah Config sudah terload
        if (typeof CONFIG === 'undefined' || !CONFIG.DATA_CABANG) {
            console.error("ERROR: File 'config.js' bermasalah atau belum diload!");
            // alert("Gagal memuat data konfigurasi. Cek Console."); // Opsional
        } else {
            console.log("Memuat data cabang...", CONFIG.DATA_CABANG);

            // A. Reset isi dropdown
            selectCabang.innerHTML = '<option value="">-- Cari Kantor Cabang --</option>';

            // B. Loop data dari CONFIG
            CONFIG.DATA_CABANG.forEach(cabang => {
                const option = document.createElement('option');
                option.value = cabang.code;
                option.textContent = cabang.name;
                selectCabang.appendChild(option);
            });

            // C. Aktifkan Tom Select (Searchable)
            if (typeof TomSelect !== 'undefined') {
                new TomSelect("#select-cabang",{
                    create: false,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: "Ketik untuk mencari cabang...",
                    plugins: ['dropdown_input'],
                    onDropdownOpen: function(){ document.body.classList.add('dropdown-open'); },
                    onDropdownClose: function(){ document.body.classList.remove('dropdown-open'); }
                });
            }
        }
    }

    // ==========================================
    // 3. EVENT LISTENERS (INTERAKSI USER)
    // ==========================================
    
    // Toggle Buka/Tutup Form
    if(toggleBtn) {
        toggleBtn.onclick = () => { 
            form.classList.toggle('hd-hidden'); 
            toggleBtn.innerText = form.classList.contains('hd-hidden') ? 'BUAT PENGAJUAN BARU' : 'TUTUP FORM';
        };
    }

    // Validasi Input KTP (Hanya Angka)
    const inputKTP = document.getElementById('id_ktp');
    if(inputKTP) {
        inputKTP.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, ''); // Hapus non-angka
            const error = document.getElementById('ktp-error');
            if (this.value.length > 0 && this.value.length !== 16) {
                error.style.display = 'block'; 
                this.setCustomValidity('Invalid');
            } else {
                error.style.display = 'none'; 
                this.setCustomValidity('');
            }
        });
    }

    // Tombol Reset
    if(btnReset) {
        btnReset.onclick = () => { 
            form.reset(); 
            // Panggil reset wilayah dari api-wilayah.js jika ada
            if(typeof window.resetWilayah === 'function') window.resetWilayah();
        };
    }

    // ==========================================
    // 4. LOGIC SUBMIT (SIMPAN KE JSON LOCAL)
    // ==========================================
    if(form) {
        form.onsubmit = async e => {
            e.preventDefault();
            
            // Cek validasi HTML native
            if(!form.checkValidity()){ form.reportValidity(); return; }

            // Cek validasi manual (Plafon)
            if(document.getElementById('error-plafon') && document.getElementById('error-plafon').style.display === 'block') {
                UI.showToast("Perbaiki nominal pinjaman!", "error"); return;
            }

            const btn = document.getElementById('btnSubmit');
            btn.disabled = true; btn.innerText = 'Memproses...';
            
            // Simulasi Loading 1.5 detik
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
                    produk_code: formData.get('jenis_produk'),
                    produk_nama: document.getElementById('product_type').options[document.getElementById('product_type').selectedIndex].text,
                    plafon: formData.get('besar_pinjaman'),
                    tenor: formData.get('jangka_waktu') + " Bulan",
                    status: "Menunggu Verifikasi Admin"
                };

                // C. SIMPAN KE LOCALSTORAGE
                let dbPengajuan = JSON.parse(localStorage.getItem('db_pengajuan_kredit') || "[]");
                dbPengajuan.push(dataJson);
                localStorage.setItem('db_pengajuan_kredit', JSON.stringify(dbPengajuan));

                // D. Tampilkan Modal Sukses (Pastikan helper.js terload)
                if(typeof UI !== 'undefined') {
                    UI.showModalSuccess(ticketId);
                } else {
                    alert("Sukses! Kode Tiket: " + ticketId);
                }

                // E. Reset Form & Tutup
                btn.disabled = false; btn.innerText = 'KIRIM PENGAJUAN';
                form.reset(); 
                if(typeof window.resetWilayah === 'function') window.resetWilayah();
                form.classList.add('hd-hidden');
                toggleBtn.innerText = 'BUAT PENGAJUAN BARU';

            }, 1500);
        };
    }

    // ==========================================
    // 5. LOGIC CEK STATUS (SEARCH JSON)
    // ==========================================
    if(btnSearch) {
        btnSearch.onclick = () => {
            const code = inputSearch.value.trim().toUpperCase();
            
            if(!code) {
                if(typeof UI !== 'undefined') UI.showToast("Masukkan kode pengajuan dulu!", "error");
                else alert("Masukkan kode pengajuan!");
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
                    if(typeof UI !== 'undefined') UI.showToast("Kode pengajuan tidak ditemukan!", "error");
                    else alert("Kode tidak ditemukan!");
                }

                btnSearch.disabled = false; btnSearch.innerText = "CEK STATUS";
            }, 800);
        };
    }
});