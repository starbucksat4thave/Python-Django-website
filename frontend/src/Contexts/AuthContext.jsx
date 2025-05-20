import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import api from '../api';

const AuthContext = createContext();

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);

    const logout = useCallback(async () => {
        try {
            await api.post('/auth/logout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            // Clear the token and expiry from localStorage
            localStorage.removeItem('authToken');
            localStorage.removeItem('authTokenExpiry');
            setUser(null);
        }
    }, []);

    const checkAuth = useCallback(async () => {
        try {
            const token = localStorage.getItem('authToken');
            const tokenExpiry = localStorage.getItem('authTokenExpiry'); // Get expiry time from localStorage

            if (!token) {
                setLoading(false);
                return;
            }

            // If token exists in localStorage, check expiry time
            if (tokenExpiry && Date.now() > tokenExpiry) {
                // If expired, log out and clear token
                await logout();
                setLoading(false);
                return;
            }

            // Verify token with backend
            const response = await api.get('/auth/user');
            if (response.data) {
                setUser(response.data);
            } else {
                await logout();
            }
        } catch (error) {
            console.error('Auth check error:', error);
            await logout();
        } finally {
            setLoading(false);
        }
    }, [logout]);

    useEffect(() => {
        checkAuth();
    }, [checkAuth]);

    const login = useCallback((token, userData, rememberMe) => {
        const storage = localStorage; // Always store in localStorage
        const tokenExpiry = rememberMe ? null : Date.now() + 2 * 60 * 60 * 1000; // 2 hours in milliseconds

        // Store token and expiry time
        storage.setItem('authToken', token);
        if (!rememberMe) {
            storage.setItem('authTokenExpiry', tokenExpiry); // Store expiry in localStorage for session-based expiration
        }

        setUser(userData);
    }, []);

    return (
        <AuthContext.Provider value={{ user, login, logout, loading }}>
            {!loading && children}
        </AuthContext.Provider>
    );
}

export const useAuth = () => {
    const context = useContext(AuthContext);
    if (!context) {
        throw new Error('useAuth must be used within an AuthProvider');
    }
    return context;
};
