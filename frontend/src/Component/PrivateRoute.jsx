import React from "react";
import { Navigate } from "react-router-dom";
import { useAuth } from "../Contexts/AuthContext";
import { CircularProgress } from "@mui/material"; // Import CircularProgress from Material UI (or use any spinner of your choice)

export default function PrivateRoute({ children }) {
    const { user, loading } = useAuth();

    if (loading) {
        return (
            <div className="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50 z-50">
                <CircularProgress size={50} className="text-white" /> {/* You can use any loading icon here */}
            </div>
        );
    }

    return user ? children : <Navigate to="/login" replace />;
}
