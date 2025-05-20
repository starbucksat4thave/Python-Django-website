import { useEffect, useState } from "react";
import { useAuth } from "../../../Contexts/AuthContext.jsx";
import { Link } from "react-router-dom";
import api from "../../../api.jsx";
import { CircularProgress } from "@mui/material"; // Import CircularProgress from MUI

const TeacherCourses = () => {
    const { user } = useAuth();
    const [courses, setCourses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchCourses = async () => {
            try {
                const response = await api.get("/courses/active", {
                    headers: { Authorization: `Bearer ${user.token}` },
                });
                setCourses(response.data.data);
            } catch (error) {
                setError(error.response?.data?.message || "Failed to fetch courses");
            } finally {
                setLoading(false);
            }
        };

        fetchCourses();
    }, [user.token]);

    return (
        <div className="p-6 bg-gray-900 text-white min-h-screen">
            <h2 className="text-2xl font-bold mb-6">My Courses</h2>

            {/* Loading Overlay */}
            {loading && (
                <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <CircularProgress size={60} thickness={4} />
                </div>
            )}

            {error && <p className="text-red-400">{error}</p>}

            {!loading && !error && courses.length === 0 && <p className="text-gray-400">No courses found.</p>}

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {courses.map((courseSession) => (
                    <div key={courseSession.id} className="bg-gray-800 p-4 rounded-lg shadow-md">
                        <h3 className="text-lg font-semibold text-gray-200">
                            {courseSession.course.code} - {courseSession.course.name}
                        </h3>
                        <p className="text-gray-400">Session: {courseSession.session}</p>

                        {/* Show Department Name */}
                        {courseSession.course.department && (
                            <p className="text-gray-400">
                                Department: {courseSession.course.department.name}
                            </p>
                        )}

                        <Link
                            to={`/teacher/courses/my-courses/${courseSession.id}`}
                            className="mt-2 inline-block text-blue-400 hover:text-blue-500"
                        >
                            View Details →
                        </Link>
                    </div>
                ))}
            </div>
        </div>
    );
};

export default TeacherCourses;
