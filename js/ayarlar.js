function selectAvatar(element) {
    var images = document.querySelectorAll('.pp-img');
    images.forEach(function(img) {
        img.classList.remove('selected');
    });

    element.classList.add('selected');

    var avatarNum = element.getAttribute('data-num');
    document.getElementById('img').value = avatarNum;
}