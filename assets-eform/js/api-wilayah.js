document.addEventListener('DOMContentLoaded', function() {
    const tomConfig = {
        valueField: 'id', labelField: 'name', searchField: 'name',
        create: false, placeholder: 'Ketik untuk mencari...', plugins: ['clear_button'],
    };

    let tsProv = new TomSelect('#select-provinsi', tomConfig);
    let tsKota = new TomSelect('#select-kota', tomConfig);
    let tsKec  = new TomSelect('#select-kecamatan', tomConfig);
    let tsKel  = new TomSelect('#select-kelurahan', tomConfig);

    const api = CONFIG.baseUrlWilayah;
    fetch(`${api}/provinces.json`).then(r => r.json()).then(d => tsProv.addOption(d));

    tsProv.on('change', function(id) {
        document.getElementById('val_provinsi').value = tsProv.options[id]?.name || '';
        tsKota.clear(); tsKota.clearOptions(); tsKota.disable();
        tsKec.clear(); tsKec.clearOptions(); tsKec.disable();
        tsKel.clear(); tsKel.clearOptions(); tsKel.disable();
        if(id){ tsKota.enable(); fetch(`${api}/regencies/${id}.json`).then(r=>r.json()).then(d=>tsKota.addOption(d)); }
    });

    tsKota.on('change', function(id) {
        document.getElementById('val_kota').value = tsKota.options[id]?.name || '';
        tsKec.clear(); tsKec.clearOptions(); tsKec.disable();
        tsKel.clear(); tsKel.clearOptions(); tsKel.disable();
        if(id){ tsKec.enable(); fetch(`${api}/districts/${id}.json`).then(r=>r.json()).then(d=>tsKec.addOption(d)); }
    });

    tsKec.on('change', function(id) {
        document.getElementById('val_kecamatan').value = tsKec.options[id]?.name || '';
        tsKel.clear(); tsKel.clearOptions(); tsKel.disable();
        if(id){ tsKel.enable(); fetch(`${api}/villages/${id}.json`).then(r=>r.json()).then(d=>tsKel.addOption(d)); }
    });

    tsKel.on('change', function(id) {
        document.getElementById('val_kelurahan').value = tsKel.options[id]?.name || '';
    });
    
    window.resetWilayah = function() { tsProv.clear(); };
});