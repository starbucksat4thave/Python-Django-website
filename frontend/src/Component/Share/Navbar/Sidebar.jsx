import { Link, useLocation } from "react-router-dom";
import {
    HomeIcon,
    BellIcon,
    BookOpenIcon,
    ChartBarIcon,
    CreditCardIcon,
    UserGroupIcon,
    ClipboardDocumentIcon,
    ArrowRightStartOnRectangleIcon,
    ChevronDownIcon,
    ChevronUpIcon,
    DocumentDuplicateIcon,
    KeyIcon,
} from "@heroicons/react/24/outline";
import { useState } from "react";
import { useAuth } from "../../../Contexts/AuthContext.jsx";

const Sidebar = ({ onLogout }) => {
    const { user } = useAuth();
    const role = user?.roles?.[0]?.name?.toLowerCase();
    const location = useLocation();

    const [studentCoursesOpen, setStudentCoursesOpen] = useState(false);
    const [teacherCoursesOpen, setTeacherCoursesOpen] = useState(false);

    return (
        <div className="fixed top-0 left-0 flex flex-col w-64 h-screen p-5 text-white bg-gray-900">
            <h2 className="mb-6 text-2xl font-bold">Portal</h2>

            <nav className="flex-1 space-y-3">
                {role === "student" && (
                    <>
                        <SidebarLink to="/student/dashboard" icon={HomeIcon} text="Dashboard" active={location.pathname.startsWith("/student/dashboard")} />

                        {/* Student Courses with Sub-Options */}
                        <button
                            className={`w-full flex items-center justify-between py-2 px-3 rounded-md transition ${studentCoursesOpen ? "bg-gray-300 text-gray-800 font-semibold" : "text-gray-300 hover:bg-gray-800"
                                }`}
                            onClick={() => setStudentCoursesOpen(!studentCoursesOpen)}
                        >
                            <div className="flex items-center space-x-3">
                                <BookOpenIcon className="w-5 h-5" />
                                <span>Courses</span>
                            </div>
                            {studentCoursesOpen ? <ChevronUpIcon className="w-5 h-5" /> : <ChevronDownIcon className="w-5 h-5" />}
                        </button>

                        {studentCoursesOpen && (
                            <div className="ml-6 space-y-2">
                                <SidebarLink to="/student/courses/all" text="All Courses" active={location.pathname === "/student/courses/all"} />
                                <SidebarLink to="/student/courses/enrolled" text="Enrolled Courses" active={location.pathname === "/student/courses/enrolled"} />
                            </div>
                        )}

                        <SidebarLink to="/student/results" icon={ChartBarIcon} text="Results" active={location.pathname.startsWith("/student/results")} />
                        <SidebarLink to="/student/payments" icon={CreditCardIcon} text="Payments" active={location.pathname.startsWith("/student/payments")} />
                        <SidebarLink to="/student/notices" icon={BellIcon} text="Notices" active={location.pathname.startsWith("/student/notices")} />
                        <SidebarLink to="/student/application" icon={ClipboardDocumentIcon} text="Application" active={location.pathname.startsWith("/student/application")} />


                    </>
                )}

                {role === "teacher" && (
                    <>
                        <SidebarLink to="/teacher/dashboard" icon={HomeIcon} text="Dashboard" active={location.pathname.startsWith("/teacher/dashboard")} />
                        <SidebarLink to="/teacher/manage-students" icon={UserGroupIcon} text="Manage Students" active={location.pathname.startsWith("/teacher/manage-students")} />
                        <SidebarLink to="/teacher/grade-assignments" icon={ClipboardDocumentIcon} text="Grade Assignments" active={location.pathname.startsWith("/teacher/grade-assignments")} />
                        <SidebarLink to="/teacher/applications" icon={DocumentDuplicateIcon} text="Applications" active={location.pathname.startsWith("/teacher/applications")} />



                        {/* Teacher Courses with Sub-Options */}
                        <button
                            className={`w-full flex items-center justify-between py-2 px-3 rounded-md transition ${teacherCoursesOpen ? "bg-gray-300 text-gray-800 font-semibold" : "text-gray-300 hover:bg-gray-800"
                                }`}
                            onClick={() => setTeacherCoursesOpen(!teacherCoursesOpen)}
                        >
                            <div className="flex items-center space-x-3">
                                <BookOpenIcon className="w-5 h-5" />
                                <span>Courses</span>
                            </div>
                            {teacherCoursesOpen ? <ChevronUpIcon className="w-5 h-5" /> : <ChevronDownIcon className="w-5 h-5" />}
                        </button>

                        {teacherCoursesOpen && (
                            <div className="ml-6 space-y-2">
                                <SidebarLink to="/student/courses/all" text="All Courses" active={location.pathname === "/student/courses/all"} />
                                <SidebarLink to="/teacher/courses/my-courses" text="My Courses" active={location.pathname === "/teacher/courses/my-courses"} />
                            </div>
                        )}
                    </>
                )}

                <button className="flex items-center mt-auto ">
                    <SidebarLink
                        to="/change-password"
                        icon={KeyIcon}
                        text="Change Password"
                        active={location.pathname.startsWith("/change-password")}
                    />
                </button>
            </nav>



            <button type="button" onClick={onLogout} className="flex items-center mt-auto text-red-400 hover:text-red-500">
                <ArrowRightStartOnRectangleIcon className="w-5 h-5 mr-2" />
                Logout
            </button>
        </div>
    );
};

// Sidebar Link Component
const SidebarLink = ({ to, icon: Icon, text, active }) => {
    return (
        <Link
            to={to}
            className={`flex items-center space-x-3 py-2 px-3 rounded-md transition ${active ? "bg-gray-300 text-gray-800 font-semibold" : "text-gray-300 hover:bg-gray-800"
                }`}
        >
            {Icon && <Icon className="w-5 h-5" />}
            <span>{text}</span>
        </Link>
    );
};

export default Sidebar;
