import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import api from "../../../api.jsx";
import { CircularProgress } from "@mui/material";
import { FaDownload, FaFileAlt } from "react-icons/fa";
import {toast} from "react-toastify";

export default function CourseResources() {
    const { courseSessionId } = useParams();
    const [resources, setResources] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchResources = async () => {
            try {
                const response = await api.get(`/course-resources/${courseSessionId}`);
                setResources(response.data.resources);
            } catch (err) {
                setError(err.response?.data?.message || "Failed to load resources.");
            } finally {
                setLoading(false);
            }
        };

        fetchResources();
    }, [courseSessionId]);

    const handleDownload = async (id, fileName) => {
        try {
            const response = await api.get(`/course-resources/download/${id}`, {
                responseType: 'blob',
            });

            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileName);
            document.body.appendChild(link);
            link.click();
            link.remove();
        } catch (err) {
            console.error(err);
            toast.error(`Download failed: ${err.response?.data?.message || err.message}`, {
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
        <div className="p-6 text-white">
            <h2 className="text-2xl font-bold mb-6">Course Resources</h2>

            {loading && (
                <div className="flex justify-center items-center mt-10">
                    <CircularProgress size={50} />
                </div>
            )}

            {error && <p className="text-red-400 text-center mt-4">{error}</p>}

            {!loading && resources.length === 0 && !error && (
                <p className="text-gray-400 text-center mt-6">No resources available for this course.</p>
            )}

            <div className="space-y-4 mt-4">
                {resources.map((resource) => (
                    <div
                        key={resource.id}
                        className="bg-gray-800 border border-gray-700 p-5 rounded-lg shadow-md flex flex-col sm:flex-row justify-between items-start sm:items-center"
                    >
                        <div className="flex-1">
                            <h3 className="text-lg font-semibold flex items-center gap-2">
                                <FaFileAlt className="text-yellow-300" />
                                {resource.title}
                            </h3>

                            {resource.description && (
                                <p className="text-gray-400 mt-1 text-sm">{resource.description}</p>
                            )}

                            <p className="text-sm text-gray-500 mt-2">
                                <strong>Type:</strong> {resource.file_type} | <strong>Size:</strong>{" "}
                                {(resource.file_size / 1024).toFixed(2)} KB
                            </p>
                        </div>

                        <button
                            onClick={() => handleDownload(resource.id, resource.file_name)}
                            className="mt-4 sm:mt-0 sm:ml-4 inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-md"
                            type="button"
                        >
                            <FaDownload className="mr-2" />
                            Download
                        </button>
                    </div>
                ))}
            </div>
        </div>
    );
}
