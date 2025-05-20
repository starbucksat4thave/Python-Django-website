import { useState, useEffect } from "react";
import api from "../../../api.jsx";
import { CircularProgress } from "@mui/material";
import { FaBook, FaCheckCircle, FaTimesCircle } from "react-icons/fa";
import {toast} from "react-toastify";

export default function Courses() {
    const [enrollments, setEnrollments] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [activeSemester, setActiveSemester] = useState(null);
    const [successMessage, setSuccessMessage] = useState("");

    const TOTAL_YEARS = 4;
    const TOTAL_SEMESTERS = 2;

    useEffect(() => {
        if (error) {
            toast.error(error, {
                position: "top-right",
                autoClose: 3000,
                hideProgressBar: true,
                closeOnClick: true,
                pauseOnHover: true,
                draggable: true,
                theme: "colored"
            });

            // Optional: clear the error after showing toast
            setError(null);
        }
    }, [error]);


    useEffect(() => {
        const fetchEnrollments = async () => {
            setLoading(true);
            try {
                const response = await api.get("/courses/active/enrollments");
                if (response.data.status === "success") {
                    setEnrollments(response.data.data);
                } else {
                    setError(response.data.message || "Failed to fetch enrollments.");
                }
            } catch (err) {
                setError(err.response?.data?.message || "An error occurred.");
            } finally {
                setLoading(false);
            }
        };

        fetchEnrollments();
    }, []);

    const allSemesters = [];
    for (let year = 1; year <= TOTAL_YEARS; year++) {
        for (let semester = 1; semester <= TOTAL_SEMESTERS; semester++) {
            allSemesters.push(`Year ${year} - Semester ${semester}`);
        }
    }

    const groupedBySemester = enrollments.reduce((acc, enrollment) => {
        const semesterKey = `Year ${enrollment.course_session.course.year} - Semester ${enrollment.course_session.course.semester}`;
        if (!acc[semesterKey]) acc[semesterKey] = [];
        acc[semesterKey].push(enrollment);
        return acc;
    }, {});

    useEffect(() => {
        if (!activeSemester && allSemesters.length > 0) {
            setActiveSemester(allSemesters[0]);
        }
    }, [allSemesters]);

    const handleReEnroll = async (courseId) => {
        try {
            setLoading(true);
            const response = await api.post("/courses/active/enroll", { course_id: courseId });

            if (response.data.status === "success") {
                setSuccessMessage("Successfully Re-Enrolled!");
                setTimeout(() => setSuccessMessage(""), 3000); // Hide after 3s

                // Refresh enrollments
                const updatedResponse = await api.get("/courses/active/enrollments");
                if (updatedResponse.data.status === "success") {
                    setEnrollments(updatedResponse.data.data);
                }
            } else {
                setError(response.data.message || "Failed to re-enroll.");
            }
        } catch (err) {
            setError(err.response?.data?.message || "An error occurred.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="p-6 relative">
            <h2 className="text-xl font-bold mb-4 text-white">Enrolled Courses</h2>

            {/* Loading Overlay */}
            {loading && (
                <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <CircularProgress size={60} thickness={4} />
                </div>
            )}

            {/* Success Floating Window at the Bottom */}
            {successMessage && (
                <div className="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg text-lg transition-opacity duration-500 opacity-100 animate-fade-out">
                    {successMessage}
                </div>
            )}

            {/* Semester Tabs */}
            <div className="flex space-x-4 border-b pb-2">
                {allSemesters.map((semester) => (
                    <button
                        key={semester}
                        className={`px-4 py-2 rounded-t-md transition-all ${
                            activeSemester === semester
                                ? "bg-gray-300 text-gray-800 font-semibold"
                                : "text-gray-300 hover:bg-gray-800"
                        }`}
                        onClick={() => setActiveSemester(semester)}
                    >
                        {semester}
                    </button>
                ))}
            </div>

            {/* Courses List */}
            <div className="mt-6 space-y-4">
                {groupedBySemester[activeSemester]?.length > 0 ? (
                    groupedBySemester[activeSemester].map((enrollment) => (
                        <div
                            key={enrollment.id}
                            className="bg-gray-900 text-white p-6 rounded-xl shadow-lg border border-gray-700 flex flex-col sm:flex-row items-start sm:items-center justify-between"
                        >
                            <div className="flex-1">
                                <h3 className="text-lg font-semibold flex items-center space-x-2">
                                    <FaBook className="text-yellow-400"/>
                                    <span>{enrollment.course_session.course.name} ({enrollment.course_session.course.code})</span>
                                </h3>

                                <div className="mt-2 text-gray-400 text-sm">
                                    <p className="flex items-center space-x-2">
                                        {enrollment.is_enrolled ? (
                                            <FaCheckCircle className="text-green-400"/>
                                        ) : (
                                            <FaTimesCircle className="text-red-400"/>
                                        )}
                                        <span>{enrollment.is_enrolled ? "Enrolled" : "Not Enrolled"}</span>
                                    </p>

                                    <p className="mt-2 text-sm text-gray-300">
                                        <strong>Session: </strong>{enrollment.course_session.session}
                                    </p>

                                    <p className="text-lg font-semibold mt-2">
                                        Class Assessment Marks: <span
                                        className="text-yellow-300">{enrollment.class_assessment_marks}</span>
                                    </p>
                                </div>
                            </div>

                            <div className="mt-4 sm:mt-0 space-y-2 sm:space-y-0 sm:space-x-2 flex flex-col sm:flex-row">
                                <a
                                    href={`/student/courses/enrolled/resources/${enrollment.course_session.id}`}
                                    className="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg text-center shadow-md"
                                >
                                    View Resources
                                </a>

                                {enrollment.canReEnroll && (
                                    <button
                                        onClick={() => handleReEnroll(enrollment.course_session.course.id)}
                                        className="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md"
                                    >
                                        Re-Enroll
                                    </button>
                                )}
                            </div>
                        </div>

                    ))
                ) : (
                    <p className="text-gray-500 text-center">No courses available</p>
                )}
            </div>
        </div>
    );
}
