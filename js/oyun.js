document.addEventListener("DOMContentLoaded", function() {

    const gameContainer = document.querySelector('.divoyun');
    const cards = document.querySelectorAll('.card');
    const timerDisplay = document.getElementById('sayac');
    const resultDisplay = document.getElementById('result');
    const startButton = document.getElementById('baslatOyun');


    let isUserLoggedIn = gameContainer.getAttribute('data-logged-in') === 'true';
    let totalPairs = cards.length / 2; // Toplam kart sayısının yarısı kadar eşleşme vardır

    let first = null; 
    let second = null; 
    let matchedCount = 0; 
    
    let sure = 0; 
    let sureLimiti = 120; 
    
    let oyunAktif = false; 
    let timerInterval; 


    timerDisplay.textContent = "0";


    startButton.addEventListener('click', function() {
        if (oyunAktif) return; 

        oyunAktif = true;
        startButton.disabled = true; 
        startButton.innerText = "Oyun Başladı...";
        resultDisplay.textContent = ""; 
        
        sure = 0;
        timerDisplay.textContent = sure;
    
        timerInterval = setInterval(() => {
            sure++; 
            timerDisplay.textContent = sure;
            
            if (sure >= sureLimiti) {
                oyunuBitir(false); 
            }
        }, 1000);
    });

    cards.forEach(card => {
        card.addEventListener('click', function() {
            if (!oyunAktif || card.classList.contains('matched') || card === first || second !== null) return;

            card.classList.add('selected');

            if (!first) {
                first = card;
            } else {
                second = card;
                if (first.dataset.id === second.dataset.id && first.dataset.type !== second.dataset.type) {
             
                    first.classList.remove('selected');
                    second.classList.remove('selected');
                    
                    first.classList.add('matched');
                    second.classList.add('matched');

                    matchedCount++;
                    first = null;
                    second = null;

                    if (matchedCount === totalPairs) {
                        oyunuBitir(true); 
                    }

                } else {
                    setTimeout(() => {
                        if (first) first.classList.remove('selected');
                        if (second) second.classList.remove('selected');
                        first = null;
                        second = null;
                    }, 500);
                }
            }
        });
    });

    function oyunuBitir(kazandiMi) {
        clearInterval(timerInterval); 
        oyunAktif = false; 
        
        if (kazandiMi) {
            resultDisplay.innerHTML = "<span style='color:green; font-weight:bold;' class='anasayfa'>Tebrikler! Oyunu " + sure + " saniyede tamamladınız. 🎉</span>";
            startButton.innerText = "Oyun Bitti (Yenile)";

            if(isUserLoggedIn) {
                let formData = new FormData();
                formData.append('ajax_skor', sure);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    console.log("Sunucu cevabı:", data);
                })
                .catch(error => console.error('Hata:', error));
            } else {
                console.log("Skor kaydedilmedi: Kullanıcı giriş yapmamış.");
            }

        } else {
            resultDisplay.innerHTML = "<span style='color:red; font-weight:bold; class='anasayfa'>Süre Doldu! " + sureLimiti + " saniyede bitiremediniz. 😞</span>";
            startButton.innerText = "Süre Doldu";
            cards.forEach(card => card.style.opacity = "0.5");
        }
        
        startButton.disabled = false;
        startButton.onclick = function() { location.reload(); };
    }
});