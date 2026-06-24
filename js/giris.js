document.addEventListener("DOMContentLoaded", function () {
    const url = new URL(window.location.href);
    if (url.searchParams.has("h")) {
        url.searchParams.delete("h");
        window.history.replaceState({}, document.title, url.toString());
    }
});