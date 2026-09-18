import axios from "axios";

/**
 * C3 · Interceptor de Axios con JWT (10 pts)
 *
 * 1. Interceptor de REQUEST: agrega el token JWT guardado en
 *    localStorage a cada petición saliente.
 * 2. Interceptor de RESPONSE: si el backend responde 401, limpia la
 *    sesión y redirige a /login.
 */

const api = axios.create({
  baseURL: "/api",
});

// --- 1. Request interceptor: adjunta el token ---
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// --- 2. Response interceptor: maneja 401 (sesión inválida/expirada) ---
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("token");
      // Si además guardas datos del usuario/sesión, límpialos también:
      // localStorage.removeItem("usuario");

      // Evita loops de redirección si ya estamos en /login.
      if (window.location.pathname !== "/login") {
        window.location.href = "/login";
      }
    }
    return Promise.reject(error);
  }
);

export default api;


