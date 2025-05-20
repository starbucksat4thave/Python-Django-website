import { useState, useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { PaperClipIcon } from '@heroicons/react/20/solid';
import CircularProgress from '@mui/material/CircularProgress';
import { toast } from 'react-toastify';
import api from '../../../api';

export default function PublicationDetail() {
    const { id } = useParams();
    const [publication, setPublication] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchPublication = async () => {
            try {
                const response = await api.get(`/publications/${id}`);
                setPublication(response.data.publication);
            } catch (err) {
                toast.error(`Download failed: ${err.response?.data?.message || err.message}`, {
                    position: "top-right",
                    autoClose: 3000,
                    hideProgressBar: true,
                    closeOnClick: true,
                    pauseOnHover: true,
                    draggable: true,
                });
            } finally {
                setLoading(false);
            }
        };

        fetchPublication();
    }, [id]);

    if (loading) {
        return (
            <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                <CircularProgress size={60} thickness={4} />
            </div>
        );
    }

    if (!publication) {
        return (
            <div className="text-center text-white mt-10">
                Publication not found
            </div>
        );
    }

    return (
        <div className="bg-gray-900 text-white p-6 rounded-lg shadow-lg max-w-4xl mx-auto mt-10">
            <h1 className="text-3xl font-bold mb-6">{publication.title}</h1>

            <dl className="mb-6 space-y-2">
                <div className="flex">
                    <dt className="w-40 font-medium">DOI:</dt>
                    <dd className="text-gray-400">
                        <a href={publication.doi} target="_blank" rel="noopener noreferrer" className="hover:underline">
                            {publication.doi}
                        </a>
                    </dd>
                </div>
                <div className="flex">
                    <dt className="w-40 font-medium">Journal:</dt>
                    <dd className="text-gray-400">{publication.journal}</dd>
                </div>
                <div className="flex">
                    <dt className="w-40 font-medium">Volume:</dt>
                    <dd className="text-gray-400">{publication.volume}</dd>
                </div>
                <div className="flex">
                    <dt className="w-40 font-medium">Pages:</dt>
                    <dd className="text-gray-400">{publication.pages}</dd>
                </div>
                <div className="flex">
                    <dt className="w-40 font-medium">Published Date:</dt>
                    <dd className="text-gray-400">{publication.published_date}</dd>
                </div>
            </dl>

            <div className="mb-6">
                <h2 className="text-xl font-semibold mb-2">Abstract</h2>
                <p className="text-gray-300">{publication.abstract}</p>
            </div>

            {publication.url && (
                <a
                    href={publication.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    <PaperClipIcon className="h-5 w-5 mr-2" />
                    View Publication
                </a>
            )}
        </div>
    );
}
