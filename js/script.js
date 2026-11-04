var toggle_theme = document.getElementById('toggle-theme');
var html = document.getElementById('html');

toggle_theme.onclick = () => {
    var newTheme = (html.getAttribute('data-bs-theme') === 'dark') ? 'light' : 'dark';

    // On change le thème visuellement
    html.setAttribute('data-bs-theme', newTheme);

    // On sauvegarde dans le cookie (valable 1 an)
    document.cookie = "theme=" + newTheme + "; path=/; max-age=31536000; SameSite=Lax";
};