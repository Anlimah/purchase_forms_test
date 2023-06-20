function flashMessage(bg_color, message) {
    const flashMessage = document.getElementById("flashMessage");

    flashMessage.classList.add(bg_color);
    flashMessage.innerHTML = message;

    setTimeout(() => {
        flashMessage.style.visibility = "visible";
        flashMessage.classList.add("show");
    }, 500);

    setTimeout(() => {
        flashMessage.classList.remove("show");
        setTimeout(() => {
            flashMessage.style.visibility = "hidden";
        }, 500);
    }, 5000);
}