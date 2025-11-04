import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Set CSRF token from meta tag if present (Laravel expects this header)
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}
// Send cookies (session) on XHR requests. Helpful for dev setups where
// the frontend and backend may be on different ports/origins.
window.axios.defaults.withCredentials = true;
