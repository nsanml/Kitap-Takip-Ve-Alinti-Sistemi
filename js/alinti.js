function begen(element, quoteId) {
    let formData = new FormData();
    formData.append('islem', 'begen');
    formData.append('quote_id', quoteId);

    fetch('../php/ajax_islem.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
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
    .catch(error => console.error('Hata:', error));
}
