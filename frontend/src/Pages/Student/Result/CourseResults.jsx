import { useState } from "react";
import { jsPDF } from "jspdf";
import autoTable from "jspdf-autotable"; // Corrected import
import api from "../../../api";

const CourseResults = () => {
    const [courses, setCourses] = useState([]);
    const [year, setYear] = useState("");
    const [semester, setSemester] = useState("");
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(false);
    const [cgpa, setCgpa] = useState(null);

    const fetchResults = async () => {
        if (!year || !semester) {
            setError("Please enter both Year and Semester.");
            return;
        }

        setLoading(true);
        setError(null);

        try {
            const response = await api.get(`/result/show-full-result/${year}/${semester}`);

            if (response.data.courses?.length > 0) {
                setCourses(response.data.courses);
                setCgpa(response.data.total_cgpa);
            } else {
                setError("No results found for the selected year and semester.");
                setCourses([]);
                setCgpa(null);
            }
        } catch (error) {
            console.error("Error fetching course results:", error);
            setError("Failed to load course results. Please try again later.");
            setCourses([]);
            setCgpa(null);
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        fetchResults();
    };

    const downloadPDF = () => {
        if (courses.length === 0) {
            alert("No course results available to generate PDF.");
            return;
        }

        const doc = new jsPDF();

        // Title
        doc.setFontSize(18);
        doc.text("Course Results", 14, 22);

        // CGPA
        doc.setFontSize(12);
        doc.text(`Semester CGPA: ${cgpa?.toFixed(2)}`, 14, 30);

        // Table data
        const tableData = courses.map((course) => [
            course.course_name,
            course.year,
            course.semester,
            course.total_marks?course.total_marks:0,
            course.grade,
            course.gpa,
            course.remark,
            course.credit_hours,
        ]);

        // Generate table using autoTable function
        autoTable(doc, {
            startY: 40,
            head: [["Course Name", "Year", "Semester", "Final Marks", "Grade", "GPA", "Remark", "Credit Hours"]],
            body: tableData,
            theme: "grid",
            styles: { fontSize: 10 },
            headStyles: {
                fillColor: [41, 128, 185],
                textColor: 255,
                fontStyle: 'bold'
            },
            alternateRowStyles: { fillColor: [245, 245, 245] },
            margin: { top: 40 },
        });

        doc.save(`course-results-${year}-${semester}.pdf`);
    };

    return (
        <div className="max-w-4xl p-6 mx-auto bg-white border rounded-lg shadow-lg">
            <h2 className="mb-6 text-2xl font-bold text-center">Course Results</h2>

            <form onSubmit={handleSubmit} className="flex flex-col items-center gap-4 mb-6">
                <div className="flex flex-col w-full gap-4 sm:flex-row sm:w-auto">
                    <input
                        type="number"
                        placeholder="Enter Year"
                        value={year}
                        onChange={(e) => setYear(e.target.value)}
                        className="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        min="1"
                        required
                    />
                    <input
                        type="number"
                        placeholder="Enter Semester"
                        value={semester}
                        onChange={(e) => setSemester(e.target.value)}
                        className="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        min="1"
                        required
                    />
                </div>
                <button
                    type="submit"
                    disabled={loading}
                    className="px-6 py-2 font-bold text-white bg-blue-500 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-blue-300"
                >
                    {loading ? "Fetching..." : "Fetch Results"}
                </button>
            </form>

            {error && <p className="mb-4 text-center text-red-500">{error}</p>}
            {loading && <p className="mb-4 text-center">Loading results...</p>}

            {courses.length > 0 && (
                <>
                    <div className="mb-6 overflow-x-auto">
                        <table className="w-full text-center border border-collapse border-gray-300">
                            <thead className="bg-gray-200">
                            <tr>
                                {["Course Name", "Year", "Semester", "Final Marks", "Grade", "GPA", "Remark", "Credit Hours"].map((header) => (
                                    <th key={header} className="px-4 py-2 border border-gray-300">
                                        {header}
                                    </th>
                                ))}
                            </tr>
                            </thead>
                            <tbody>
                            {courses.map((course) => (
                                <tr key={course.course_id} className="hover:bg-gray-50">
                                    <td className="px-4 py-2 border border-gray-300">{course.course_name}</td>
                                    <td className="px-4 py-2 border border-gray-300">{course.year}</td>
                                    <td className="px-4 py-2 border border-gray-300">{course.semester}</td>
                                    <td className="px-4 py-2 border border-gray-300">{course.total_marks?course.total_marks:0}</td>
                                    <td className={`px-4 py-2 border border-gray-300 font-bold ${course.grade === "F" ? "text-red-600" : "text-green-600"}`}>
                                        {course.grade}
                                    </td>
                                    <td className="px-4 py-2 border border-gray-300">{course.gpa}</td>
                                    <td className={`px-4 py-2 border border-gray-300 font-semibold ${course.remark === "Fail" ? "text-red-500" : "text-green-500"}`}>
                                        {course.remark}
                                    </td>
                                    <td className="px-4 py-2 border border-gray-300">{course.credit_hours}</td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="mb-4 text-center">
                        <p className="text-lg font-semibold">
                            Semester CGPA: <span className="text-blue-600">{cgpa?.toFixed(2)}</span>
                        </p>
                    </div>

                    <div className="flex justify-center">
                        <button
                            onClick={downloadPDF}
                            className="px-6 py-2 font-bold text-white bg-green-500 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            Download PDF
                        </button>
                    </div>
                </>
            )}

            {courses.length === 0 && !loading && !error && (
                <p className="text-center">No course results available.</p>
            )}
        </div>
    );
};

export default CourseResults;