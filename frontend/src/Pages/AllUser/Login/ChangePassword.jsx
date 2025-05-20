import { useState } from 'react';
import api from '../../../api.jsx';
import { useAuth } from '../../../Contexts/AuthContext.jsx';

export default function ChangePassword() {
  const [form, setForm] = useState({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  });
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const { token } = useAuth(); // Assuming your AuthContext provides token

  const handleChange = (e) => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage('');
    setError('');
    setIsLoading(true);

    try {
      await api.post('/auth/change-password', form, {
        headers: {
          Authorization: `Bearer ${token}`
        }
      });
      setMessage('✅ Password changed successfully!');
      setForm({ current_password: '', new_password: '', new_password_confirmation: '' });
    } catch (err) {
      setError(err.response?.data?.message || '❌ Error changing password.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen px-4 text-white bg-gray-900">
      <div className="w-full max-w-md p-8 bg-gray-800 shadow-lg rounded-2xl">
        <h2 className="mb-6 text-2xl font-bold text-center">Change Password</h2>

        {message && <div className="p-3 mb-4 text-green-700 bg-green-100 rounded">{message}</div>}
        {error && <div className="p-3 mb-4 text-red-700 bg-red-100 rounded">{error}</div>}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block mb-1 text-sm font-medium text-gray-300">Current Password</label>
            <input
              type="password"
              name="current_password"
              value={form.current_password}
              onChange={handleChange}
              required
              className="w-full px-4 py-3 text-white bg-gray-900 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Enter current password"
            />
          </div>

          <div>
            <label className="block mb-1 text-sm font-medium text-gray-300">New Password</label>
            <input
              type="password"
              name="new_password"
              value={form.new_password}
              onChange={handleChange}
              required
              className="w-full px-4 py-3 text-white bg-gray-900 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Enter new password"
            />
          </div>

          <div>
            <label className="block mb-1 text-sm font-medium text-gray-300">Confirm New Password</label>
            <input
              type="password"
              name="new_password_confirmation"
              value={form.new_password_confirmation}
              onChange={handleChange}
              required
              className="w-full px-4 py-3 text-white bg-gray-900 border border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Confirm new password"
            />
          </div>

          <button
            type="submit"
            disabled={isLoading}
            className="w-full py-3 font-semibold text-white transition bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50"
          >
            {isLoading ? 'Changing Password...' : 'Change Password'}
          </button>
        </form>
      </div>
    </div>
  );
}
