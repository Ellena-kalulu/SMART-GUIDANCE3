<div class="space-y-6">
    <!-- Summary Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-blue-700">{{ $data['total_students'] ?? 0 }}</p>
            <p class="text-xs text-black/60">Total Students</p>
        </div>
        <div class="text-blue-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-blue-700">{{ $data['completed_assessments'] ?? 0 }}</p>
            <p class="text-xs text-black/60">Assessments Completed</p>
        </div>
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-blue-700">{{ $data['completion_rate'] ?? 0 }}%</p>
            <p class="text-xs text-black/60">Completion Rate</p>
        </div>
        <div class="text-blue-50 rounded-lg p-4 text-center">
            <p class="text-2xl font-bold text-blue-700">{{ count($data['career_interests'] ?? []) }}</p>
            <p class="text-xs text-black/60">Career Categories</p>
        </div>
    </div>

    <!-- Career Interests Distribution -->
    <div>
        <h3 class="font-semibold text-black/80 mb-3">🎯 Career Interests Distribution</h3>
        <div class="space-y-2">
            @foreach(($data['career_interests'] ?? []) as $category => $count)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span>{{ $category }}</span>
                    <span>{{ $count }} students ({{ round(($count / max($data['total_students'], 1)) * 100) }}%)</span>
                </div>
                <div class="w-full h-2 bg-black/[0.05] rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full" style="width: {{ ($count / max($data['total_students'], 1)) * 100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Top Careers -->
    <div>
        <h3 class="font-semibold text-black/80 mb-3">🏆 Top Career Matches</h3>
        <table class="w-full">
            <thead class="bg-white">
                <tr><th class="px-4 py-2 text-left text-xs font-semibold">Career</th><th class="px-4 py-2 text-left text-xs font-semibold">Category</th><th class="px-4 py-2 text-center text-xs font-semibold">Matches</th><th class="px-4 py-2 text-center text-xs font-semibold">Avg Score</th></tr>
            </thead>
            <tbody>
                @foreach(($data['top_careers'] ?? []) as $career)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $career->recommended->title ?? 'N/A' }}</td>
                    <td class="px-4 py-2">{{ $career->recommended->category ?? 'N/A' }}</td>
                    <td class="px-4 py-2 text-center">{{ $career->count }}</td>
                    <td class="px-4 py-2 text-center">{{ round($career->avg_score, 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
