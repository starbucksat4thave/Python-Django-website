import { useEffect, useState } from "react";
import { useParams, Link } from "react-router-dom";
import api from "../../../api.jsx";
import { CircularProgress } from "@mui/material";

const TeacherCourseDetails = () => {
    const { courseSessionId } = useParams();
    const [courseSession, setCourseSession] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const [pastSessions, setPastSessions] = useState([]);
    const [loadingPast, setLoadingPast] = useState(false);
    const [errorPast, setErrorPast] = useState(null);
    const [showPastSessions, setShowPastSessions] = useState(false); // Toggle state

    useEffect(() => {
        const fetchCourseDetails = async () => {
            try {
                const response = await api.get(`/courses/active/${courseSessionId}`);
                setCourseSession(response.data.data);
            } catch (error) {
                setError(error.response?.data?.message || "Failed to fetch course details.");
            } finally {
                setLoading(false);
            }
        };

        fetchCourseDetails();
    }, [courseSessionId]);

    const fetchPastSessions = async () => {
        if (showPastSessions) {
            setShowPastSessions(false);
            setPastSessions([]);
            return;
        }

        setLoadingPast(true);
        try {
            const response = await api.get(`/courses/active/${courseSessionId}/past-sessions`);
            setPastSessions(response.data.data);
            setShowPastSessions(true);
        } catch (error) {
            setErrorPast(error.response?.data?.message || "Failed to fetch past sessions.");
        } finally {
            setLoadingPast(false);
        }
    };

    return (
        <div className="p-6 bg-gray-900 text-white min-h-screen">
            <h2 className="text-2xl font-bold mb-6">Course Details</h2>

            {loading && (
                <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <CircularProgress size={60} thickness={4} />
                </div>
            )}

            {error && <p className="text-red-400">{error}</p>}

            {courseSession && (
                <div className="bg-gray-800 p-6 rounded-lg shadow-md">
                    <h3 className="text-lg font-semibold text-gray-200">
                        {courseSession.course.code} - {courseSession.course.name}
                    </h3>
                    <p className="text-gray-400">Session: {courseSession.session}</p>
                    <p className="text-gray-400">Credits: {courseSession.course.credit}</p>
                    <p className="text-gray-400">Year: {courseSession.course.year}</p>
                    <p className="text-gray-400">Semester: {courseSession.course.semester}</p>
                    <p className="text-gray-400">
                        Description: {courseSession.course.description || "No description available"}
                    </p>

                    {/* Buttons Section */}
                    <div className="mt-6 flex flex-wrap gap-4">
                        <button
                            onClick={fetchPastSessions}
                            className={`px-4 py-2 rounded-md transition-all duration-200 ${
                                showPastSessions
                                    ? "bg-red-600 hover:bg-red-700"
                                    : "bg-blue-600 hover:bg-blue-700"
                            } text-white`}
                        >
                            {showPastSessions ? "Hide Past Sessions" : "View Past Sessions"}
                        </button>

                        <Link
                            to={`/grade-assignments/${courseSessionId}`}
                            className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md"
                        >
                            Grade Assignments
                        </Link>

                        {/* New Button: Course Resources */}
                        <Link
                            to={`/teacher/courses/my-courses/${courseSessionId}/resources`}
                            className="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md"
                        >
                            Course Resources
                        </Link>
                    </div>

                    {/* Past Sessions Section */}
                    {loadingPast && <CircularProgress size={30} thickness={4} className="mt-4" />}
                    {errorPast && <p className="text-red-400 mt-4">{errorPast}</p>}

                    {showPastSessions && pastSessions.length > 0 && (
                        <div className="mt-6">
                            <h3 className="text-xl font-semibold text-gray-200">Past Sessions</h3>
                            <ul className="mt-4 space-y-2">
                                {pastSessions.map((session) => (
                                    <li key={session.id} className="bg-gray-700 p-3 rounded-md">
                                        <p className="text-gray-300">Session: {session.session}</p>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
};

export default TeacherCourseDetails;
