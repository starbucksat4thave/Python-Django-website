import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { CircularProgress } from '@mui/material';
import { toast } from 'react-toastify';
import { EnvelopeIcon, CalendarIcon, PhoneIcon, AcademicCapIcon, BuildingLibraryIcon, MapPinIcon, IdentificationIcon, UserIcon } from '@heroicons/react/24/outline';
import Dashboard from "../../../Component/Dashboard.jsx";
import CollapsibleSection from "../../../Component/CollapsibleSection.jsx";
import { useAuth } from "../../../Contexts/AuthContext.jsx";
import api from "../../../api.jsx";

export default function StudentDashboard() {
    const { user } = useAuth();
    const [publications, setPublications] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const navigate = useNavigate();

    useEffect(() => {
        const fetchPublications = async () => {
            setLoading(true);
            setError(null);
            try {
                const response = await api.get('/publications');
                setPublications(response.data.publications);
            } catch (err) {
                console.error('Error fetching publications:', err);
                setError('Failed to fetch publications');
                toast.error(`Error: ${err.response?.data?.message || err.message}`, {
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

        fetchPublications();
    }, []);

    return (
        <div className="flex justify-center items-center min-h-screen bg-gray-900">
            <div className="shadow-lg rounded-lg w-full max-w-5xl p-2">
                <Dashboard user={user}>
                    <CollapsibleSection title="Personal Information">
                        <DetailItem icon={EnvelopeIcon} label="Email" value={user?.email} />
                        <DetailItem icon={CalendarIcon} label="Date of Birth" value={user?.dob} />
                        <DetailItem icon={PhoneIcon} label="Phone" value={user?.phone} />
                        <DetailItem icon={MapPinIcon} label="City" value={user?.city} />
                        <DetailItem icon={MapPinIcon} label="Address" value={user?.address} />
                    </CollapsibleSection>

                    <CollapsibleSection title="Academic Information">
                        <DetailItem icon={IdentificationIcon} label="University ID" value={user?.university_id} />
                        <DetailItem icon={BuildingLibraryIcon} label="Session" value={user?.session} />
                        <DetailItem
                            icon={AcademicCapIcon}
                            label="Semester"
                            value={`Year ${user?.year}, Semester ${user?.semester}`}
                        />
                    </CollapsibleSection>

                    <CollapsibleSection title="Publications" defaultOpen={true}>
                        {loading ? (
                            <div className="flex justify-center items-center">
                                <CircularProgress size={60} thickness={4} />
                            </div>
                        ) : error ? (
                            <div className="text-center text-red-500 mt-4">{error}</div>
                        ) : publications.length > 0 ? (
                            publications.map((pub) => (
                                <div key={pub.id} className="bg-gray-800 p-3 rounded mb-2 flex justify-between items-center">
                                    <div className="mr-4">
                                        <p className="text-white font-semibold">{pub.title}</p>
                                        <p className="text-gray-400 text-sm">{pub.journal}</p>
                                    </div>
                                    <button
                                        className="text-blue-400 hover:text-blue-600 whitespace-nowrap"
                                        onClick={() => navigate(`/publications/${pub.id}`)}
                                    >
                                        View Details
                                    </button>
                                </div>
                            ))
                        ) : (
                            <p className="text-gray-400">No publications found.</p>
                        )}
                    </CollapsibleSection>

                    <CollapsibleSection title="Department Information">
                        <DetailItem icon={BuildingLibraryIcon} label="Department" value={user?.department?.name} />
                        <DetailItem icon={UserIcon} label="Faculty" value={user?.department?.faculty} />
                        <DetailItem icon={IdentificationIcon} label="Short Name" value={user?.department?.short_name} />
                    </CollapsibleSection>
                </Dashboard>
            </div>
        </div>
    );
}

const DetailItem = ({ icon: Icon, label, value }) => (
    <div className="flex items-center space-x-3 text-gray-300">
        <Icon className="h-5 w-5 text-gray-400" />
        <p className="font-medium">{label}:</p>
        <p className="text-white">{value || "N/A"}</p>
    </div>
);
