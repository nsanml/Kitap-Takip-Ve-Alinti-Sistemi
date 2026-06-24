document.addEventListener('DOMContentLoaded', function() {

    const ppImages = document.querySelectorAll('.pp-img');
    const hiddenInput = document.getElementById('img');

    if (ppImages.length > 0) {
        ppImages.forEach(img => {
            img.addEventListener('click', function() {
                ppImages.forEach(i => i.classList.remove('selected'));
                
                this.classList.add('selected');
                
                if(hiddenInput) {
                    hiddenInput.value = this.dataset.num;
                }
            });
        });
    }

    const url = new URL(window.location.href);
    const paramsToRemove = ['pw', 'mail', 'bos'];
    let changed = false;

    paramsToRemove.forEach(param => {
        if (url.searchParams.has(param)) {
            url.searchParams.delete(param);
            changed = true;
        }
    });

    if (changed) {
        window.history.replaceState({}, document.title, url.toString());
    }
});