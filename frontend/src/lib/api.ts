const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000";

export async function checkBackendHealth(): Promise<boolean> {
  try {
    const res = await fetch(`${API_URL}/up`, { cache: "no-store" });
    return res.ok;
  } catch {
    return false;
  }
}

export function getApiUrl(path = ""): string {
  const base = API_URL.replace(/\/$/, "");
  const suffix = path.startsWith("/") ? path : `/${path}`;
  return `${base}${suffix}`;
}
