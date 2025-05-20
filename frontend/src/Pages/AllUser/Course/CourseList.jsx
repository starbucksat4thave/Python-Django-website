import React, { useState, useEffect } from "react";
import api from "../../../api.jsx"; // Ensure Axios is configured properly
import { CircularProgress } from "@mui/material";

export default function CoursesList() {
    const [courses, setCourses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [activeSemester, setActiveSemester] = useState(null);

    // Define total number of years and semesters
    const TOTAL_YEARS = 4;
    const TOTAL_SEMESTERS = 2;

    useEffect(() => {
        const fetchCourses = async () => {
            try {
                const response = await api.get("/courses");
                if (response.data.status === "success") {
                    setCourses(response.data.data);
                } else {
                    setError(response.data.message || "Failed to fetch courses.");
                }
            } catch (err) {
                setError(err.response?.data?.message || "An error occurred.");
            } finally {
                setLoading(false);
            }
        };

        fetchCourses();
    }, []);

    // Generate all possible year-semester combinations
    const allSemesters = [];
    for (let year = 1; year <= TOTAL_YEARS; year++) {
        for (let semester = 1; semester <= TOTAL_SEMESTERS; semester++) {
            allSemesters.push({ key: `${year}-${semester}`, label: `Year ${year} - Semester ${semester}` });
        }
    }

    // Group courses by semester
    const groupedCourses = {};
    allSemesters.forEach(({ key }) => (groupedCourses[key] = [])); // Initialize empty arrays

    courses.forEach(course => {
        const key = `${course.year}-${course.semester}`;
        if (groupedCourses[key]) {
            groupedCourses[key].push(course);
        }
    });

    // Set default active semester when data loads
    useEffect(() => {
        if (!activeSemester && allSemesters.length > 0) {
            setActiveSemester(allSemesters[0].key);
        }
    }, [allSemesters]);

    return (
        <div className="p-6 bg-gray-900 text-white min-h-screen">
            <h2 className="text-2xl font-bold mb-6">All Courses</h2>

            {/* Loading Overlay */}
            {loading && (
                <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <CircularProgress size={60} thickness={4} />
                </div>
            )}

            {error && <p className="text-red-400">{error}</p>}

            {/* Semester Tabs */}
            <div className="flex space-x-4 border-b pb-2 overflow-x-auto">
                {allSemesters.map(({ key, label }) => (
                    <button
                        key={key}
                        className={`px-4 py-2 rounded-t-md transition-all ${
                            activeSemester === key
                                ? "bg-gray-300 text-gray-800 font-semibold"
                                : "text-gray-300 hover:bg-gray-800"
                        }`}
                        onClick={() => setActiveSemester(key)}
                    >
                        {label}
                    </button>
                ))}
            </div>

            {/* Course List for Active Semester */}
            <div className="mt-4 space-y-3">
                {groupedCourses[activeSemester]?.length > 0 ? (
                    groupedCourses[activeSemester].map((course) => (
                        <div key={course.id} className="bg-gray-800 p-4 rounded-lg shadow-md">
                            <h3 className="text-lg font-semibold text-gray-200">
                                {course.code} - {course.name}
                            </h3>
                            <p className="text-gray-400">Credits: {course.credit}</p>
                            <p className="text-gray-400">Description: {course.description || "N/A"}</p>
                        </div>
                    ))
                ) : (
                    <p className="text-gray-500">No courses available</p>
                )}
            </div>
        </div>
    );
}
