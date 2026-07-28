<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-blue-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-blue-700">Priority</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-black/70">Student Name</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-black/70">Form</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-black/70">Stream</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-black/70">Assessment Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-black/70">Match Score</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-black/70">Recommended Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-black/10">
            @forelse($students as $student)
            <tr class="hover:bg-white">
                <td class="px-4 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                        {{ $student['priority'] === 'High' ? 'text-blue-100 text-blue-700' : 'text-blue-100 text-blue-700' }}">
                        {{ $student['priority'] }}
                    </span>
                </td>
                <td class="px-4 py-3 font-medium">{{ $student['name'] }}</td>
                <td class="px-4 py-3">{{ $student['form'] }}</td>
                <td class="px-4 py-3">{{ $student['stream'] }}</td>
                <td class="px-4 py-3">
                    @if($student['has_assessment'])
                        <span class="bg-blue-600">✓ Completed</span>
                    @else
                        <span class="text-blue-600">✗ Not Started</span>
                    @endif
                </td>
                <td class="px-4 py-3">{{ $student['best_match_score'] }}%</td>
                <td class="px-4 py-3">
                    <span class="text-sm {{ $student['priority'] === 'High' ? 'text-blue-600 font-medium' : 'text-blue-600' }}">
                        {{ $student['recommended_action'] }}
                    </span>
                </td>
             </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-black/50">No students needing intervention at this time.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
