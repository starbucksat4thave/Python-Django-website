import React, { useState, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import axios from "axios";
import api from "../../../api.jsx";

export default function ResetPassword() {
    const navigate = useNavigate();
    const location = useLocation();

    // Extract token & email from URL
    const queryParams = new URLSearchParams(location.search);
    const token = queryParams.get("token");
    const email = queryParams.get("email");

    const [password, setPassword] = useState("");
    const [confirmPassword, setConfirmPassword] = useState("");
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState("");
    const [error, setError] = useState("");

    const [isMinLengthValid, setIsMinLengthValid] = useState(false);
    const [isNumberValid, setIsNumberValid] = useState(false);
    const [isCheckboxChecked, setIsCheckboxChecked] = useState(false);

    useEffect(() => {
        if (!token || !email) {
            setError("Invalid or missing reset token.");
        }
    }, [token, email]);

    const validatePassword = (password) => {
        const minLength = password.length >= 8;
        const hasNumber = /\d/.test(password);

        setIsMinLengthValid(minLength);
        setIsNumberValid(hasNumber);

        // Set checkboxes based on validation
        setIsCheckboxChecked(minLength && hasNumber);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError("");
        setMessage("");
        setLoading(true);

        if (password !== confirmPassword) {
            setError("Passwords do not match.");
            setLoading(false);
            return;
        }

        if (!isMinLengthValid || !isNumberValid) {
            setError("Password must be at least 8 characters long and contain a number.");
            setLoading(false);
            return;
        }

        try {
            const response = await api.post('/auth/reset-password', {
                token,
                email,
                password,
                password_confirmation: confirmPassword,
            });


            setMessage("Password reset successful! Redirecting...");
            setTimeout(() => navigate("/login"), 2000);
        } catch (err) {
            setError(err.response?.data?.message || "Something went wrong.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="flex items-center justify-center min-h-screen bg-gray-900 text-white px-6">
            <div className="bg-gray-800 shadow-lg rounded-lg p-8 w-full max-w-md">
                <h2 className="text-2xl font-semibold text-center text-gray-200 mb-4">Reset Your Password</h2>

                {message && <p className="text-green-500 text-center">{message}</p>}
                {error && <p className="text-red-500 text-center">{error}</p>}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label className="block text-sm font-medium text-gray-300">New Password</label>
                        <input
                            type="password"
                            className="mt-1 w-full px-4 py-3 border border-gray-700 rounded-md shadow-sm text-gray-200 bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter new password"
                            value={password}
                            onChange={(e) => {
                                setPassword(e.target.value);
                                validatePassword(e.target.value); // Validate on change
                            }}
                            required
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-300">Confirm Password</label>
                        <input
                            type="password"
                            className="mt-1 w-full px-4 py-3 border border-gray-700 rounded-md shadow-sm text-gray-200 bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Confirm new password"
                            value={confirmPassword}
                            onChange={(e) => setConfirmPassword(e.target.value)}
                            required
                        />
                    </div>

                    {/* Custom Checkboxes for Password Validations */}
                    <div className="space-y-3 mt-4">
                        {/* Minimum Length Validation */}
                        <div className="flex items-center space-x-3">
                            <div className={`h-5 w-5 rounded-full border-2 flex items-center justify-center 
            ${isMinLengthValid ? 'border-green-500 bg-green-500' : 'border-gray-400'}`}>
                                {isMinLengthValid && (
                                    <svg className="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                )}
                            </div>
                            <label className="text-sm text-gray-300">
                                Password must be at least 8 characters long.
                            </label>
                        </div>

                        {/* Number Validation */}
                        <div className="flex items-center space-x-3">
                            <div className={`h-5 w-5 rounded-full border-2 flex items-center justify-center 
            ${isNumberValid ? 'border-green-500 bg-green-500' : 'border-gray-400'}`}>
                                {isNumberValid && (
                                    <svg className="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                    </svg>
                                )}
                            </div>
                            <label className="text-sm text-gray-300">
                                Password must contain at least one number.
                            </label>
                        </div>
                    </div>


                    {/* Submit Button with Loading State */}
                    <button
                        type="submit"
                        className="w-full bg-indigo-600 text-white py-3 rounded-md text-sm font-semibold hover:bg-indigo-700 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        disabled={loading || !isMinLengthValid || !isNumberValid}
                    >
                        {loading ? (
                            <>
                                <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white" />
                                Resetting...
                            </>
                        ) : (
                            "Reset Password"
                        )}
                    </button>
                </form>
            </div>
        </div>
    );
}
