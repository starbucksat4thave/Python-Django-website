import { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import api from "../../../api";

const Notices = () => {
    const [notices, setNotices] = useState([]);
    const [filteredNotices, setFilteredNotices] = useState([]);
    const [departments, setDepartments] = useState([]);
    const [selectedDepartment, setSelectedDepartment] = useState("");

    useEffect(() => {
        api.get("/show-notice")
            .then(response => {
                setNotices(response.data.notices);
                setFilteredNotices(response.data.notices);

                // Extract unique departments
                const uniqueDepartments = [
                    ...new Set(response.data.notices.map(notice => notice.department_name))
                ];
                setDepartments(uniqueDepartments);
            })
            .catch(error => console.error("Error fetching notices:", error));
    }, []);

    // Handle department filtering
    const handleFilterChange = (event) => {
        const department = event.target.value;
        setSelectedDepartment(department);
        
        if (department === "") {
            setFilteredNotices(notices);
        } else {
            setFilteredNotices(notices.filter(notice => notice.department_name === department));
        }
    };

    return (
        <div>
            <h2 className="mb-4 text-lg font-bold">Notices</h2>

            {/* Department Filter Dropdown */}
            <div className="mb-4">
                <label className="mr-2 font-semibold">Filter by Department:</label>
                <select value={selectedDepartment} onChange={handleFilterChange} className="p-2 border rounded">
                    <option value="">All Departments</option>
                    {departments.map((dept, index) => (
                        <option key={index} value={dept}>{dept}</option>
                    ))}
                </select>
            </div>

            {filteredNotices.length > 0 ? (
                <ul>
                    {filteredNotices.map((notice) => (
                        <li key={notice.id} className="p-4 mb-2 border rounded-lg shadow">
                            <h3 className="font-semibold text-md">
                                <Link to={`/notices/${notice.id}`} className="text-blue-600 hover:underline">
                                    {notice.title}
                                </Link>
                            </h3>
                            <p>{notice.content}</p>
                            <small className="text-gray-500">
                                Department: {notice.department_name} | {new Date(notice.created_at).toLocaleDateString()}
                            </small>
                        </li>
                    ))}
                </ul>
            ) : (
                <p>No notices available.</p>
            )}
        </div>
    );
};

export default Notices;
