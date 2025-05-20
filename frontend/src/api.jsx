import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_BACKEND_API_URL || 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    withCredentials: true
});

// Add token to every request
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('authToken');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Handle expired tokens
api.interceptors.response.use(
    response => response,
    error => {
        // Skip redirect for login requests
        if (error.response?.status === 401 && !error.config?._skipAuthRedirect) {
            localStorage.removeItem('authToken');
            sessionStorage.removeItem('authToken');
            window.location = '/login';
        }
        return Promise.reject(error);
    }
);

export default api;