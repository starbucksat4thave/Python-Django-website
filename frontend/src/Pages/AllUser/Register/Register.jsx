import { useState, useContext } from "react";
import { useNavigate, Link } from "react-router-dom";
import { toast, ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import { AuthContext } from "../../../Layout/AuthProvider/AuthProvider";
import { motion } from "framer-motion";

const SignUp = () => {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [role, setRole] = useState("user");
  const [image, setImage] = useState(null);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const authContext = useContext(AuthContext);

  if (!authContext) {
    throw new Error("SignUp must be used within an AuthProvider");
  }

  const { createUser, updateUserProfile } = authContext;

  const handleSignUp = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const user = await createUser(email, password, name);
      if (image) {
        const formData = new FormData();
        formData.append("image", image);
        const response = await fetch("/api/upload-image", {
          method: "POST",
          body: formData,
        });
        if (response.ok) {
          const { imageUrl } = await response.json();
          await updateUserProfile(name, imageUrl, role);
        }
      } else {
        await updateUserProfile(name, undefined, role);
      }
      toast.success("Successfully signed up");
      navigate("/login");
    } catch (error) {
      toast.error("Failed to sign up");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">
      <motion.div 
        initial={{ opacity: 0, scale: 0.9 }}
        animate={{ opacity: 1, scale: 1 }}
        transition={{ duration: 0.5 }}
        className="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
        <h2 className="text-center text-3xl font-bold text-indigo-600">Sign Up</h2>
        <form onSubmit={handleSignUp} className="space-y-4 mt-6">
          <div>
            <label className="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" className="mt-1 p-2 w-full border rounded-md" value={name} onChange={(e) => setName(e.target.value)} required />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" className="mt-1 p-2 w-full border rounded-md" value={email} onChange={(e) => setEmail(e.target.value)} required />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" className="mt-1 p-2 w-full border rounded-md" value={password} onChange={(e) => setPassword(e.target.value)} required />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Profile Image</label>
            <input type="file" className="mt-1 p-2 w-full border rounded-md" onChange={(e) => setImage(e.target.files ? e.target.files[0] : null)} accept="image/*" />
          </div>
          <button type="submit" className="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none" disabled={loading}>
            {loading ? "Signing up..." : "Sign Up"}
          </button>
        </form>
        <p className="text-center mt-4">Already have an account? <Link to="/login" className="text-indigo-500 font-semibold hover:underline">Login</Link></p>
      </motion.div>
      <ToastContainer position="top-center" autoClose={5000} hideProgressBar pauseOnHover theme="light" />
    </div>
  );
};

export default SignUp;
