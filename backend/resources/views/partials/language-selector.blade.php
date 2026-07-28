<form action="{{ route('locale.set') }}" method="POST" class="flex items-center gap-1.5">
    @csrf
    <select name="locale" onchange="this.form.submit()"
            class="rounded-lg border border-black/10 text-xs py-1.5 px-2 bg-white text-black/70 focus:ring-2 focus:ring-brand-200"
            aria-label="Select language">
        <option value="en" @selected(session('locale', 'en') === 'en')>English</option>
        <option value="ny" @selected(session('locale') === 'ny')>Chichewa</option>
        <option value="tum" @selected(session('locale') === 'tum')>Chitumbuka</option>
    </select>
</form>
