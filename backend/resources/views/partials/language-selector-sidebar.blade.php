<form action="{{ route('locale.set') }}" method="POST" class="px-2">
    @csrf
    <select name="locale" onchange="this.form.submit()"
            class="sidebar-locale-select w-full rounded-lg border border-blue-200/50 text-xs py-2 px-2
                   bg-white/10 text-white/80 focus:ring-2 focus:ring-blue-400 focus:border-blue-300"
            aria-label="{{ __('parent.language') }}">
        <option value="en" @selected(session('locale', 'en') === 'en')>English</option>
        <option value="ny" @selected(session('locale') === 'ny')>Chichewa</option>
        <option value="tum" @selected(session('locale') === 'tum')>Chitumbuka</option>
    </select>
</form>
