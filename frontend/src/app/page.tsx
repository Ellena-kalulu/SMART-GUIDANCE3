import Link from "next/link";
import { checkBackendHealth, getApiUrl } from "@/lib/api";

export default async function HomePage() {
  const backendOnline = await checkBackendHealth();
  const apiUrl = getApiUrl();

  return (
    <div className="min-h-screen hero-gradient">
      <header className="border-b border-white/60 bg-white/70 backdrop-blur-md sticky top-0 z-50">
        <div className="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold text-lg">L</div>
            <div>
              <p className="font-bold text-slate-900 leading-tight">Luwinga Secondary School</p>
              <p className="text-xs text-slate-500">Smart Career Guidance</p>
            </div>
          </div>
          <nav className="flex items-center gap-3">
            <Link href="/login" className="px-4 py-2 text-sm font-medium text-slate-700 hover:text-blue-600">Sign In</Link>
            <Link href="/register" className="px-4 py-2 text-sm font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700">Register</Link>
          </nav>
        </div>
      </header>

      <main className="max-w-6xl mx-auto px-6 py-16">
        <div className="text-center mb-12">
          <span className="inline-block px-4 py-1.5 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold mb-6">Luwinga Career Guidance Portal</span>
          <h1 className="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-6">
            Smart Career &amp; Subject Guidance
          </h1>
          <p className="text-lg text-slate-600 max-w-2xl mx-auto mb-8">
            Discover your strengths, explore career paths, and choose the right subject combinations for your future.
          </p>
          <div className="flex flex-wrap justify-center gap-4">
            <Link href="/login" className="px-8 py-3.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-200">Get Started</Link>
            <a href={`${apiUrl}/`} target="_blank" rel="noopener noreferrer" className="px-8 py-3.5 bg-white text-slate-700 font-semibold rounded-xl border border-slate-200 hover:border-blue-300">Legacy Portal (Blade)</a>
          </div>
        </div>

        <div className="grid md:grid-cols-3 gap-6 mb-12">
          {[
            { title: "Career Assessment", desc: "RIASEC-based assessments to match you with suitable careers." },
            { title: "Subject Combinations", desc: "MSCE-aligned recommendations for Form 3 and beyond." },
            { title: "Progress Tracking", desc: "Monitor academic progress and career goals over time." },
          ].map((card) => (
            <div key={card.title} className="p-6 rounded-2xl bg-white/80 backdrop-blur border border-white shadow-sm">
              <h3 className="font-bold text-slate-900 mb-2">{card.title}</h3>
              <p className="text-sm text-slate-600">{card.desc}</p>
            </div>
          ))}
        </div>

        <div className={`p-4 rounded-xl text-sm flex items-center gap-3 ${backendOnline ? "bg-green-50 border border-green-200 text-green-800" : "bg-amber-50 border border-amber-200 text-amber-800"}`}>
          <span className={`w-2.5 h-2.5 rounded-full ${backendOnline ? "bg-green-500" : "bg-amber-500"}`} />
          {backendOnline
            ? `Backend API is online at ${apiUrl}`
            : `Backend API is offline. Start it with: cd backend && php artisan serve`}
        </div>
      </main>
    </div>
  );
}
