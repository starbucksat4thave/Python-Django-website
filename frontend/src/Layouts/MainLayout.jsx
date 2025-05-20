import Sidebar from "../Component/Share/Navbar/Sidebar.jsx";
import { Outlet, useNavigate } from "react-router-dom";
import { useAuth } from "../Contexts/AuthContext.jsx";

export default function MainLayout() {
    const { logout } = useAuth();
    const navigate = useNavigate();

    const handleLogout = () => {
        logout();
        navigate("/login");
    };

    return (
        <div className="flex flex-col md:flex-row">
            <Sidebar onLogout={handleLogout} className="w-full md:w-64" />
            <div className="flex-1 p-1 md:ml-64">
                <Outlet />
            </div>
        </div>
    );
}
