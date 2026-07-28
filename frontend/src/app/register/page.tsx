export default function RegisterPage() {
  const apiUrl = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000";
  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50 px-4">
      <div className="w-full max-w-md bg-white rounded-2xl shadow-lg p-8 border border-slate-100">
        <h1 className="text-2xl font-black text-center mb-2">Create Account</h1>
        <p className="text-center text-slate-500 mb-6 text-sm">Join Luwinga Career Guidance</p>
        <p className="text-sm text-slate-600 bg-blue-50 border border-blue-100 rounded-xl p-4">
          Registration UI will connect to the Laravel API. For now, use the{" "}
          <a href={`${apiUrl}/register`} className="text-blue-600 font-medium hover:underline">legacy register page</a>{" "}
          on the backend while pages are migrated to Next.js.
        </p>
      </div>
    </div>
  );
}
