import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../../../api.jsx';
import { useAuth } from '../../../Contexts/AuthContext.jsx';
import { EyeIcon, EyeSlashIcon } from "@heroicons/react/24/outline";

export default function Login() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [rememberMe, setRememberMe] = useState(false);
    const [error, setError] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const { login } = useAuth();
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setIsLoading(true);

        try {
            const response = await api.post('/auth/login', {
                email,
                password,
                remember_me: rememberMe
            }, {
                _skipAuthRedirect: true
            });

            login(response.data.token, response.data.user, rememberMe);
            const mainRole = response.data.user.roles[0]?.name.toLowerCase();

            if (mainRole === 'student') {
                navigate('/student/dashboard');
            } else if (mainRole === 'teacher') {
                navigate('/teacher/dashboard');
            } else {
                console.warn('Unexpected or missing user role:', mainRole);
                navigate('/login');
            }
        } catch (err) {
            console.error("Login error:", err.response?.data);
            if (err.config.url.includes('/auth/login')) {
                if (err.response?.status === 401) {
                    setError('Invalid email or password');
                } else {
                    setError('Login failed. Please try again.');
                }
            }
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-900 text-white px-4">
            <div className="w-full max-w-md bg-gray-800 backdrop-blur-lg shadow-xl rounded-2xl p-8">
                <h2 className="text-center text-3xl font-bold text-gray-200">Sign in to your account</h2>
                <p className="text-center text-sm text-gray-400 mt-2">
                    Welcome back! Please enter your details.
                </p>

                <form onSubmit={handleSubmit} className="mt-6 space-y-5">
                    {/* Email Input */}
                    <div>
                        <label htmlFor="email" className="block text-sm font-medium text-gray-300">
                            Email Address
                        </label>
                        <input
                            id="email"
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            required
                            className="mt-1 w-full px-4 py-3 border border-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>

                    {/* Password Input */}
                    <div className="relative">
                        <label htmlFor="password" className="block text-sm font-medium text-gray-300">
                            Password
                        </label>
                        <input
                            id="password"
                            type={showPassword ? "text" : "password"}
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            required
                            className="mt-1 w-full px-4 py-3 border border-gray-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 pr-10"
                        />
                        <button
                            type="button"
                            onClick={() => setShowPassword(!showPassword)}
                            className="absolute inset-y-0 right-3 flex items-center mt-7"
                        >
                            {showPassword ? (
                                <EyeSlashIcon className="h-5 w-5 text-gray-500 hover:text-gray-700 transition duration-200" />
                            ) : (
                                <EyeIcon className="h-5 w-5 text-gray-500 hover:text-gray-700 transition duration-200" />
                            )}
                        </button>
                    </div>

                    {/* Remember Me & Forgot Password */}
                    <div className="flex items-center justify-between">
                        <div className="flex items-center">
                            <input
                                id="remember-me"
                                type="checkbox"
                                checked={rememberMe}
                                onChange={(e) => setRememberMe(e.target.checked)}
                                className="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                            />
                            <label htmlFor="remember-me" className="ml-2 text-sm text-gray-300">
                                Remember me
                            </label>
                        </div>
                        <a href={"/forgot-password"} className="text-sm text-indigo-600 hover:underline">
                            Forgot password?
                        </a>
                    </div>

                    {/* Error Message */}
                    {error && (
                        <div className="bg-red-100 text-red-700 p-3 rounded-lg">
                            {error}
                        </div>
                    )}

                    {/* Submit Button with Loading State */}
                    <button
                        type="submit"
                        disabled={isLoading}
                        className="w-full bg-indigo-600 text-white py-3 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        {isLoading ? (
                            <>
                                <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white" />
                                Signing In...
                            </>
                        ) : (
                            'Sign in'
                        )}
                    </button>
                </form>

                {/* Sign Up Link */}
                <p className="text-center text-sm text-gray-400 mt-5">
                    Don't have an account?{" "}
                    <a href="#" className="text-indigo-600 hover:underline">
                        Sign up
                    </a>
                </p>
            </div>
        </div>
    );
}
