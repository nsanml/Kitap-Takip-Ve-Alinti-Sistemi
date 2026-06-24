function yorumDuzenleAc(id, icerik) {
    document.getElementById('duzenle_yorum_id').value = id;
    document.getElementById('duzenle_yorum_icerik').value = icerik;
   
    var myModal = new bootstrap.Modal(document.getElementById('yorumDuzenleModal'));
    myModal.show();
}

function takipEt(profilId, islem) {
    let btn = document.getElementById('btn-takip');
    let takipciSpan = document.getElementById('takipci-sayisi');
    let formData = new FormData();
    formData.append('profil_id', profilId);
    formData.append('islem', islem);

    fetch('../php/ajax_takip.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            takipciSpan.innerText = data.yeni_sayi;
            location.reload(); 
        }
    });
}

function modalSifirla() {
    document.getElementById('modalBaslik').innerText = "Yeni Alıntı Ekle";
    document.getElementById('modal_islem').value = "ekle";
    document.getElementById('modal_alinti_id').value = "";
    document.getElementById('alintiForm').reset();
}

function alintiDuzenle(id, content, bookId) {
    document.getElementById('modalBaslik').innerText = "Alıntıyı Düzenle";
    document.getElementById('modal_islem').value = "guncelle";
    document.getElementById('modal_alinti_id').value = id;
    document.getElementById('modal_content').value = content;
    document.getElementById('modal_book_id').value = bookId;
    var myModal = new bootstrap.Modal(document.getElementById('alintiModal'));
    myModal.show();
}

function alintiKaydet() {
    let islem = document.getElementById('modal_islem').value;
    let alintiId = document.getElementById('modal_alinti_id').value;
    let bookId = document.getElementById('modal_book_id').value;
    let content = document.getElementById('modal_content').value;

    let formData = new FormData();
    formData.append('islem', islem);
    formData.append('alinti_id', alintiId);
    formData.append('book_id', bookId);
    formData.append('content', content);

    fetch('../php/ajax_alinti_islem.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') { location.reload(); } else { alert(data.message); }
    });
}

function alintiSil(id) {
    if(confirm("Bu alıntıyı silmek istediğinize emin misiniz?")) {
        let formData = new FormData();
        formData.append('islem', 'sil');
        formData.append('alinti_id', id);

        fetch('../php/ajax_alinti_islem.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') { location.reload(); } else { alert(data.message); }
        });
    }
}
