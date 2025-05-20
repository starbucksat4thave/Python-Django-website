import {useEffect, useState} from "react";
import {useParams} from "react-router-dom";
import api from "../../../api.jsx";
import {CircularProgress} from "@mui/material";
import {FaDownload, FaTrash, FaEdit} from "react-icons/fa";

export default function TeacherCourseResources() {
    const {courseSessionId} = useParams();
    const [resources, setResources] = useState([]);
    const [loading, setLoading] = useState(true);
    const [uploading, setUploading] = useState(false);
    const [file, setFile] = useState(null);
    const [title, setTitle] = useState("");
    const [description, setDescription] = useState("");
    const [error, setError] = useState(null);
    const [updatingId, setUpdatingId] = useState(null);

    const fetchResources = async () => {
        setLoading(true);
        try {
            const response = await api.get(`/course-resources/${courseSessionId}`);
            setResources(response.data.resources);
        } catch (err) {
            setError(err.response?.data?.message || "Failed to load resources.");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchResources();
    }, [courseSessionId]);

    const handleUpload = async (e) => {
        e.preventDefault();
        if (!title || (!file && !updatingId)) {
            setError("Title is required. File is required for new uploads.");
            return;
        }

        setUploading(true);
        setError(null);
        const formData = new FormData();
        formData.append("title", title);
        formData.append("description", description);
        if (file) formData.append("file", file);

        try {
            if (updatingId) {
                // Spoof PUT using POST and _method
                formData.append("_method", "PUT");
                await api.post(`/course-resources/${updatingId}`, formData, {
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                });
            } else {
                await api.post("/course-resources/upload", formData, {
                    params: {course_session_id: courseSessionId},
                    headers: {
                        "Content-Type": "multipart/form-data",
                    },
                });
            }
            setTitle("");
            setDescription("");
            setFile(null);
            setUpdatingId(null);
            await fetchResources();
        } catch (err) {
            setError(err.response?.data?.message || "Upload failed.");
        } finally {
            setUploading(false);
        }
    };
    const handleDelete = async (id) => {
        if (!confirm("Are you sure you want to delete this resource?")) return;

        try {
            await api.delete(`/course-resources/${id}`);
            setResources((prev) => prev.filter((r) => r.id !== id));
        } catch (err) {
            console.log(err);
            alert("Failed to delete resource.");
        }
    };

    const startUpdate = (resource) => {
        setTitle(resource.title);
        setDescription(resource.description || "");
        setFile(null);
        setUpdatingId(resource.id);
        window.scrollTo({top: 0, behavior: "smooth"});
    };

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
            alert("Download failed: " + (err.response?.data?.message || err.message));
            console.error(err);
        }
    };

    return (
        <div className="p-6 bg-gray-900 text-white min-h-screen">
            <h2 className="text-2xl font-bold mb-6">Manage Course Resources</h2>

            {/* Upload/Update Form */}
            <form onSubmit={handleUpload} className="mb-10 bg-gray-800 p-6 rounded-lg shadow-md">
                <h3 className="text-lg font-semibold mb-4">
                    {updatingId ? "Update Resource" : "Upload New Resource"}
                </h3>

                {error && <p className="text-red-400 mb-4">{error}</p>}
                <div className="grid gap-4">
                    <input
                        type="text"
                        placeholder="Title"
                        value={title}
                        onChange={(e) => setTitle(e.target.value)}
                        className="p-3 rounded-md bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    />

                    <textarea
                        placeholder="Description (optional)"
                        value={description}
                        onChange={(e) => setDescription(e.target.value)}
                        className="p-3 rounded-md bg-gray-700 text-white border border-gray-600 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                        rows={3}
                    />

                    <label className="flex flex-col gap-1 text-gray-300">
                        <span>Choose File</span>
                        <input
                            type="file"
                            onChange={(e) => setFile(e.target.files[0])}
                            className="file:bg-blue-600 file:hover:bg-blue-700 file:text-white file:px-4 file:py-2 file:rounded-md
                       file:border-none bg-gray-700 text-white rounded-md border border-gray-600 focus:outline-none"
                        />
                    </label>

                    <div className="flex gap-3">
                        <button
                            type="submit"
                            disabled={uploading}
                            className="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md text-white"
                        >
                            {uploading
                                ? updatingId
                                    ? "Updating..."
                                    : "Uploading..."
                                : updatingId
                                    ? "Update Resource"
                                    : "Upload Resource"}
                        </button>
                        {updatingId && (
                            <button
                                type="button"
                                onClick={() => {
                                    setTitle("");
                                    setDescription("");
                                    setFile(null);
                                    setUpdatingId(null);
                                }}
                                className="bg-gray-600 hover:bg-gray-700 px-4 py-2 rounded-md text-white"
                            >
                                Cancel
                            </button>
                        )}
                    </div>
                </div>

            </form>

            {/* Resource List */}
            <h3 className="text-xl font-semibold mb-4">Uploaded Resources</h3>

            {loading ? (
                <div className="flex justify-center items-center">
                    <CircularProgress size={40}/>
                </div>
            ) : resources.length === 0 ? (
                <p className="text-gray-400">No resources uploaded yet.</p>
            ) : (
                <div className="space-y-4">
                    {resources.map((resource) => (
                        <div
                            key={resource.id}
                            className="bg-gray-800 p-4 rounded-lg flex flex-col sm:flex-row justify-between items-start sm:items-center"
                        >
                            <div>
                                <h4 className="text-lg font-semibold">{resource.title}</h4>
                                {resource.description && (
                                    <p className="text-gray-400 text-sm">{resource.description}</p>
                                )}
                                <p className="text-gray-500 text-sm mt-1">
                                    <strong>Type:</strong> {resource.file_type} |{" "}
                                    <strong>Size:</strong> {(resource.file_size / 1024).toFixed(2)} KB
                                </p>
                            </div>

                            <div className="flex gap-2 mt-4 sm:mt-0">
                                <button
                                    onClick={() => handleDownload(resource.id, resource.file_name)}
                                    className="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm flex items-center gap-1"
                                >
                                    <FaDownload/>
                                    Download
                                </button>
                                <button
                                    onClick={() => startUpdate(resource)}
                                    className="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded-md text-sm flex items-center gap-1"
                                >
                                    <FaEdit/>
                                    Update
                                </button>
                                <button
                                    onClick={() => handleDelete(resource.id)}
                                    className="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm flex items-center gap-1"
                                >
                                    <FaTrash/>
                                    Delete
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
