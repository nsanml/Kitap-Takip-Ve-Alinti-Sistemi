function createSnowflake() {
    const snowflake = document.createElement('div');
    snowflake.classList.add('snowflake');
    snowflake.innerHTML = '❄'; 
    
    snowflake.style.left = Math.random() * 100 + 'vw';
    snowflake.style.fontSize = (Math.random() * 15 + 10) + 'px';
    snowflake.style.animationDuration = (Math.random() * 3 + 3) + 's'; 
    
    document.body.appendChild(snowflake);

    setTimeout(() => {
        snowflake.remove();
    }, 6000);
}
setInterval(createSnowflake, 150);
