console.log('script.js loaded');

document.addEventListener('DOMContentLoaded', function() {
    var navbar = document.querySelector('.header .flex .navbar');
    var profile = document.querySelector('.profile');

    console.log('navbar:', navbar); // Verificar si se seleccionó correctamente
    console.log('profile:', profile); // Verificar si se seleccionó correctamente

    document.querySelector('#menu-btn').onclick = () => {
        console.log('#menu-btn clicked');
        debugger; // Añadir debugger para pausar la ejecución y permitir inspección
        navbar.classList.toggle('active');
        profile.classList.remove('active');
    };

    document.querySelector('#user-btn').onclick = () => {
        console.log('#user-btn clicked');
        debugger; // Añadir debugger para pausar la ejecución y permitir inspección
        profile.classList.toggle('active');
        navbar.classList.remove('active');
    };

    window.onscroll = () => {
        console.log('window scrolled');
        navbar.classList.remove('active');
        profile.classList.remove('active');
    };
});

function loader() {
    console.log('loader function called');
    document.querySelector('.loader').style.display = 'none';
}

function fadeOut() {
    console.log('fadeOut function called');
    setInterval(loader, 2000);
}

window.onload = fadeOut;

document.querySelectorAll('input[type="number"]').forEach(numberInput => {
    numberInput.oninput = () => {
        if (numberInput.value.length > numberInput.maxLength) 
            numberInput.value = numberInput.value.slice(0, numberInput.maxLength);
    };
});
