import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import api from "../../../api";

const NoticeDetails = () => {
    const { id } = useParams();
    const [notice, setNotice] = useState(null);
    const [error, setError] = useState(null);

    useEffect(() => {
        api.get(`/show-notice/${id}`, {
            headers: {
                Authorization: `Bearer ${localStorage.getItem('token')}`,
            },
        })
            .then(response => {
                setNotice(response.data.notice.notice);
            })
            .catch(error => {
                console.error("Error fetching notice:", error);
                setError("Notice not found.");
            });
    }, [id]);

    if (error) {
        return <p className="text-center text-red-500">{error}</p>;
    }

    if (!notice) {
        return <p className="text-center">Loading...</p>;
    }

    // Define file path for the image
    const fileUrl = notice.file ? `http://127.0.0.1:8000/storage/${notice.file}` : null;
    const fileExtension = fileUrl ? fileUrl.split('.').pop().toLowerCase() : "";

    return (
        <div className="flex items-center justify-center min-h-screen p-6">
            <div className="w-full max-w-4xl p-6 border rounded-lg shadow-lg">
                <h2 className="text-2xl font-semibold text-center">{notice.title}</h2>
                <p className="mt-2 text-center">{notice.content}</p>

                <div className="mt-4">
                    <p className="text-center"><strong>Published On:</strong> {notice.published_on ? new Date(notice.published_on).toLocaleDateString() : "Not Published"}</p>
                    <p className="text-center"><strong>Archived On:</strong> {notice.archived_on ? new Date(notice.archived_on).toLocaleDateString() : "Not Archived"}</p>
                    <p className="text-center"><strong>Department:</strong> {notice.department.name}</p>
                </div>

                {fileUrl && (
                    <div className="mt-6 text-center">
                        <p className="mt-[30px]"><strong>Attached File:</strong></p>
                        
                        {/* Container to center the image */}
                        <div className="flex items-center justify-center">
                            {/* Show image if the file is an image */}
                            {["jpg", "jpeg", "png", "gif", "webp"].includes(fileExtension) ? (
                                <img 
                                    src={fileUrl} 
                                    alt="Notice Attachment" 
                                    className="h-auto max-w-full rounded-lg shadow mt-7"
                                    onError={(e) => {
                                        console.error("Image failed to load:", e.target.src);
                                        e.target.style.display = "none";
                                    }}
                                />
                            ) : null}
                        </div>

                        {/* Show embedded PDF if the file is a PDF */}
                        {fileExtension === "pdf" ? (
                            <iframe 
                                src={fileUrl} 
                                width="100%" 
                                height="500px" 
                                className="mt-2 border rounded"
                                title="Notice Attachment"
                            ></iframe>
                        ) : null}

                        {/* Provide download/view link for all file types */}
                        <a href={fileUrl} target="_blank" rel="noopener noreferrer" className="block mt-4 text-blue-600 underline">
                            View / Download File
                        </a>
                    </div>
                )}
            </div>
        </div>
    );
};

export default NoticeDetails;