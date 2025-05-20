import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import api from "../../../api.jsx";
import { CircularProgress } from "@mui/material";

const GradeAssignments = () => {
    const { courseSessionId } = useParams();
    const [enrollments, setEnrollments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [successMessage, setSuccessMessage] = useState("");

    useEffect(() => {
        const fetchEnrollments = async () => {
            try {
                const response = await api.get(`/courses/active/enrollments/${courseSessionId}`);
                setEnrollments(response.data.data.map((enrollment) => ({
                    ...enrollment,
                    class_assessment_marks: enrollment.class_assessment_marks || 0,
                    final_term_marks: enrollment.final_term_marks || 0,
                })));
            } catch (error) {
                setError(error.response?.data?.message || "Failed to fetch enrollments.");
            } finally {
                setLoading(false);
            }
        };

        fetchEnrollments();
    }, [courseSessionId]);

    const handleMarksChange = (e, index, field) => {
        const updated = [...enrollments];
        updated[index][field] = e.target.value;
        setEnrollments(updated);
    };

    const handleSave = async () => {
        setLoading(true);
        try {
            const response = await api.post(`/courses/active/enrollments/updateMarks`, {
                courseSession_id: courseSessionId,
                enrollments: enrollments.map(({ id, class_assessment_marks, final_term_marks }) => ({
                    id,
                    class_assessment_marks,
                    final_term_marks,
                })),
            });

            setSuccessMessage("Marks updated successfully!");
            setTimeout(() => setSuccessMessage(""), 3000); // Hide after 3s
        } catch (error) {
            setError(error.response?.data?.message || "Failed to update marks.");
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="p-6 bg-gray-900 text-white min-h-screen relative">
            <h2 className="text-2xl font-bold mb-6">Grade Assignments</h2>

            {loading && (
                <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <CircularProgress size={60} thickness={4} />
                </div>
            )}

            {/* Floating Success Message */}
            {successMessage && (
                <div className="fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg text-lg transition-opacity duration-500 opacity-100">
                    {successMessage}
                </div>
            )}

            {error && <p className="text-red-400">{error}</p>}

            <div className="overflow-x-auto shadow-md rounded-lg">
                <table className="min-w-full bg-gray-800 text-gray-200">
                    <thead>
                    <tr className="text-left border-b border-gray-700">
                        <th className="px-4 py-3">University ID</th>
                        <th className="px-4 py-3">Student Name</th>
                        <th className="px-4 py-3">Class Assessment Marks</th>
                        <th className="px-4 py-3">Final Term Marks</th>
                    </tr>
                    </thead>
                    <tbody>
                    {enrollments.map((enrollment, index) => (
                        <tr key={enrollment.id} className="border-b border-gray-700">
                            <td className="px-4 py-3">{enrollment.student.university_id}</td>
                            <td className="px-4 py-3">{enrollment.student.name}</td>
                            <td className="px-4 py-3">
                                <input
                                    type="number"
                                    value={enrollment.class_assessment_marks}
                                    onChange={(e) => handleMarksChange(e, index, "class_assessment_marks")}
                                    className="w-full px-3 py-2 text-gray-900 bg-white border border-gray-400 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </td>
                            <td className="px-4 py-3">
                                <input
                                    type="number"
                                    value={enrollment.final_term_marks}
                                    onChange={(e) => handleMarksChange(e, index, "final_term_marks")}
                                    className="w-full px-3 py-2 text-gray-900 bg-white border border-gray-400 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </table>
            </div>

            <button
                onClick={handleSave}
                className="mt-6 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md"
            >
                Save Marks
            </button>
        </div>
    );
};

export default GradeAssignments;
