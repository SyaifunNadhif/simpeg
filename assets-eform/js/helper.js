function formatRupiah(angka) {
    let number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    return split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
}

function cleanNumber(str) {
    return parseInt(str.replace(/\./g, '') || 0);
}

function toRupiahLabel(num) {
    return "Rp " + new Intl.NumberFormat('id-ID').format(Math.round(num));
}

const UI = {
    showToast: (msg, type = 'success') => {
        let container = document.querySelector('.hd-toast-wrap');
        if (!container) {
            container = document.createElement('div');
            container.className = 'hd-toast-wrap';
            document.body.appendChild(container);
        }
        const t = document.createElement('div');
        t.className = 'hd-toast ' + type;
        t.innerHTML = `<div>${msg}</div><div class="close" onclick="this.parentElement.remove()">✕</div>`;
        container.appendChild(t);
        requestAnimationFrame(() => t.classList.add('show'));
        setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3000);
    },
    showModalSuccess: (ticket) => {
        let overlay = document.createElement('div');
        overlay.className = 'hd-modal-overlay show';
        overlay.innerHTML = `
          <div class="hd-modal">
            <h3 style="color:#064e3b; margin-bottom:10px;">Pengajuan Berhasil!</h3>
            <p>Data Anda telah kami terima.</p>
            <div class="hd-ticket-box">${ticket}</div>
            <button class="hd-btn hd-btn-primary" onclick="this.closest('.hd-modal-overlay').remove()">TUTUP</button>
          </div>`;
        document.body.appendChild(overlay);
    }
};