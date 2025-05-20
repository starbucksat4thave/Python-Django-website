import {
    UserIcon,
    AcademicCapIcon,
    ArrowDownTrayIcon
} from "@heroicons/react/24/outline";
import api from "../api"; // adjust this path if needed
import { saveAs } from "file-saver";
import {toast} from "react-toastify"; // make sure you install this

export default function Dashboard({ user, children }) {
    const handleDownloadIdCard = async () => {
        try {
            const response = await api.get("/id-card", {
                responseType: "blob", // expect binary data
            });

            const blob = new Blob([response.data], { type: "application/pdf" });
            const filename = `ID_Card_${user.university_id}.pdf`;
            saveAs(blob, filename);
        } catch (error) {
            console.error("Failed to download ID card:", error);
            toast.error("Failed to download ID card.", {
                position: "top-right",
                autoClose: 3000,
                hideProgressBar: true,
                closeOnClick: true,
                pauseOnHover: true,
                draggable: true,
            });
        }
    };

    return (
        <div className="min-h-screen w-full bg-gray-900 text-white p-8">
            {/* Header/Profile */}
            <div className="flex flex-wrap items-center justify-between mb-6">
                <div className="flex items-center space-x-6">
                    <img
                        src={user?.image_url || user?.image || "https://via.placeholder.com/150"}
                        alt="Profile"
                        className="w-24 h-24 rounded-full border border-gray-600"
                    />
                    <div>
                        <h2 className="text-3xl font-bold text-white flex items-center gap-2">
                            <UserIcon className="h-7 w-7 text-gray-400" />
                            {user?.name}
                        </h2>
                        <p className="text-gray-400 flex items-center gap-2 text-lg">
                            <AcademicCapIcon className="h-5 w-5 text-gray-400" />
                            {user?.designation}
                        </p>
                    </div>
                </div>

                {/* Download ID Card Button */}
                <button
                    onClick={handleDownloadIdCard}
                    className="flex items-center gap-2 px-4 py-2 mt-4 sm:mt-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition"
                >
                    <ArrowDownTrayIcon className="h-5 w-5" />
                    Download ID Card
                </button>
            </div>

            {/* Main Content */}
            <div className="space-y-6 border-t-2 border-gray-700 pt-6">
                {children}
            </div>
        </div>
    );
}
