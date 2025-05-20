import {useEffect, useState} from 'react';
import api from '../../../api.jsx'; // Ensure that api.jsx includes bearer token logic
import ApplicationForm from '../../../Component/ApplicationForm.jsx';
import {CircularProgress} from '@mui/material';
import {toast} from 'react-toastify';
import {ArrowDownTrayIcon, PaperClipIcon} from '@heroicons/react/24/outline';

const Application = () => {
    const [templates, setTemplates] = useState([]);
    const [selectedTemplate, setSelectedTemplate] = useState(null);
    const [placeholders, setPlaceholders] = useState([]);
    const [myApplications, setMyApplications] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    // Show errors using toast
    useEffect(() => {
        if (error) {
            toast.error(error, {
                position: 'top-right',
                autoClose: 3000,
                hideProgressBar: true,
                closeOnClick: true,
                pauseOnHover: true,
                draggable: true,
                theme: 'colored',
            });
            setError(null);
        }
    }, [error]);

    // Fetch templates and user applications on initial load
    useEffect(() => {
        fetchTemplates();
        fetchMyApplications();
    }, []);

    // Fetch application templates
    const fetchTemplates = async () => {
        setLoading(true);
        try {
            const res = await api.get('/applications/templates');
            setTemplates(res.data.data);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to load templates.');
        } finally {
            setLoading(false);
        }
    };

    const downloadAttachment = async (appId) => {
        setLoading(true);
        try {
            const res = await api.get(`/applications/${appId}/attachment`, {
                responseType: 'blob',
            });

            const blob = new Blob([res.data]);
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `attachment_${appId}.pdf`; // Adjust extension if needed
            a.click();
            window.URL.revokeObjectURL(url);
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to download attachment.');
        } finally {
            setLoading(false);
        }
    };


    // Fetch user's applications
    const fetchMyApplications = async () => {
        setLoading(true);
        try {
            const res = await api.get('/applications/my-applications');
            setMyApplications(res.data.data);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to load applications.');
        } finally {
            setLoading(false);
        }
    };

    // Handle template click
    const handleTemplateClick = async (templateId) => {
        setLoading(true);
        try {
            const res = await api.get(`/applications/templates/${templateId}`);
            setSelectedTemplate(res.data.data);
            setPlaceholders(res.data.data.placeholders);
        } catch (err) {
            setError(err.response?.data?.message || 'Failed to load template details.');
        } finally {
            setLoading(false);
        }
    };

    // Handle form submission
    const handleApplicationSubmitted = () => {
        fetchMyApplications();
        setSelectedTemplate(null);
        setPlaceholders([]);
    };

    // Function to render template body with placeholders in red
    const renderTemplatePreview = (templateBody) => {
        const regex = /%([^%]+)%/g;  // Match text between % %
        const updatedTemplate = templateBody.replace(regex, (match, content) => {
            return `<span class="text-red-500">${content}</span>`;  // Apply red styling to placeholders
        });
        return updatedTemplate;
    };

    const downloadApplication = async (appId) => {
        setLoading(true);
        try {
            const res = await api.get(`/applications/${appId}/download`, {
                responseType: 'blob',
            });

            const blob = new Blob([res.data]);
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `application_${appId}.pdf`; // Change extension if not PDF
            a.click();
            window.URL.revokeObjectURL(url);
        } catch (err) {
            toast.error(err.response?.data?.message || 'Failed to download approved application.');
        } finally {
            setLoading(false);
        }
    };


    return (
        <div className="p-6 min-h-screen bg-gray-900 text-white relative">
            {/* Loading Overlay */}
            {loading && (
                <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <CircularProgress size={60} thickness={4}/>
                </div>
            )}

            <h2 className="text-2xl font-bold mb-6">My Applications</h2>

            <div className="overflow-x-auto mb-10">
                <table className="min-w-full bg-gray-800 shadow rounded-lg">
                    <thead>
                    <tr className="bg-gray-700 text-left text-sm font-semibold text-gray-300">
                        <th className="px-4 py-3">Title</th>
                        <th className="px-4 py-3">Applied At</th>
                        <th className="px-4 py-3">Status</th>
                        <th className="px-4 py-3">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    {myApplications.map(app => (
                        <tr key={app.id} className="border-t border-gray-700 hover:bg-gray-700/50">
                            <td className="px-4 py-3">{app.application_template.title}</td>
                            <td className="px-4 py-3">{new Date(app.created_at).toLocaleDateString()}</td>
                            <td className="px-4 py-3 capitalize">{app.status}</td>
                            <td className="px-4 py-3 flex items-center gap-3">
                                {/* Application Download (conditionally rendered or placeholder) */}
                                {app.status === 'approved' && app.authorized_copy ? (
                                    <button
                                        onClick={() => downloadApplication(app.id)}
                                        className="flex items-center text-blue-400 hover:text-blue-300 mr-4"
                                        title="Download Approved Copy"
                                    >
                                        <ArrowDownTrayIcon className="w-5 h-5 mr-1"/>
                                        Application
                                    </button>
                                ) : (
                                    // Invisible placeholder to keep attachment aligned
                                    <div className="w-[130px]"></div> // Adjust width if needed
                                )}

                                {/* Attachment Download (always shown) */}
                                {app.attachment && (
                                    <button
                                        onClick={() => downloadAttachment(app.id)}
                                        className="flex items-center text-green-400 hover:text-green-300"
                                        title="Download Attachment"
                                    >
                                        <PaperClipIcon className="w-5 h-5 mr-1"/>
                                        Attachment
                                    </button>
                                )}
                            </td>


                        </tr>
                    ))}
                    </tbody>
                </table>
            </div>

            <h3 className="text-xl font-semibold mb-4">Available Templates</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
                {templates.map(template => (
                    <div
                        key={template.id}
                        className="p-4 bg-gray-800 shadow rounded-lg border border-gray-700 flex justify-between items-center"
                    >
                        <span className="font-medium">{template.title}</span>
                        <button
                            onClick={() => handleTemplateClick(template.id)}
                            className="px-4 py-1 text-sm bg-blue-600 hover:bg-blue-500 rounded text-white"
                        >
                            Apply
                        </button>
                    </div>
                ))}
            </div>

            {selectedTemplate && (
                <div className="bg-gray-800 p-6 shadow rounded-lg border border-gray-700">
                    <h4 className="text-lg font-semibold mb-2">Template Preview: {selectedTemplate.title}</h4>
                    <div
                        className="bg-gray-900 p-4 rounded text-sm text-gray-300 mb-6 whitespace-pre-wrap overflow-auto"
                        dangerouslySetInnerHTML={{__html: renderTemplatePreview(selectedTemplate.body)}} // Render the template with red placeholders
                    />
                    <ApplicationForm
                        templateId={selectedTemplate.id}
                        placeholders={placeholders}
                        onSubmitSuccess={handleApplicationSubmitted}
                    />
                </div>
            )}
        </div>
    );
};

export default Application;
