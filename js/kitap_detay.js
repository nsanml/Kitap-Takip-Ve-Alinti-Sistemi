function durumGuncelle(durum) {
    document.querySelectorAll('.btn-durum').forEach(btn => btn.classList.remove('active'));
    if(event && event.target) {
        event.target.classList.add('active');
    }
    const urlParams = new URLSearchParams(window.location.search);
    const bookId = urlParams.get('id');

    if (!bookId) {
        alert("Kitap ID bulunamadı!");
        return;
    }

    let formData = new FormData();
    formData.append('book_id', bookId);
    formData.append('status', durum);

    fetch('../php/ajax_kitap_durum.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(r => r.json())
    .then(d => { 
        if(d.status !== 'success') { 
            alert('Hata: ' + d.message); 
        }
    })
    .catch(err => console.error("Hata:", err));
}


function begen(element, quoteId) {
    if(element.style.pointerEvents === 'none') return;
    element.style.pointerEvents = 'none';

    let formData = new FormData();
    formData.append('islem', 'begen');
    formData.append('quote_id', quoteId);

    fetch('ajax_islem.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(r => r.json())
    .then(data => {
        element.style.pointerEvents = 'auto'; 
        
        if (data.status === 'success') {
            document.getElementById('like-count-' + quoteId).innerText = data.yeni_sayi;
            
            let iconSpan = element.querySelector('.icon');
            if (data.islem_turu === 'begendi') { 
                element.classList.add('liked'); 
                iconSpan.innerText = '❤️'; 
            } else { 
                element.classList.remove('liked'); 
                iconSpan.innerText = '🤍'; 
            }
        } else {
            alert(data.message || 'Bir hata oluştu');
        }
    })
    .catch(err => {
        console.error('Hata:', err);
        element.style.pointerEvents = 'auto';
    });
}

function openEditModal(id, content) {
    document.getElementById('edit_yorum_id').value = id;
    document.getElementById('edit_yorum_icerik').value = content;
    
    var modalElement = document.getElementById('editCommentModal');
    var myModal = new bootstrap.Modal(modalElement);
    myModal.show();
}