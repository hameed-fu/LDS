<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Certificate;
use Illuminate\Support\Carbon;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public int $daysRange = 7; // used for activity sparkline / recent stats

    // Top-level stats
    public function stats(): array
    {
        return [
            'users'        => User::count(),
            'courses'      => Course::count(),
            'quizzes'      => Quiz::count(),
            'attempts'     => QuizAttempt::count(),
            'certificates' => Certificate::count(),
        ];
    }

    // Recent quiz attempts
    public function recentAttempts()
    {
        return QuizAttempt::with(['user', 'quiz'])
            ->latest('attempted_at')
            ->limit(8)
            ->get();
    }

    // Latest certificates
    public function latestCertificates()
    {
        return Certificate::with(['user', 'course'])
            ->latest('issued_at')
            ->limit(6)
            ->get();
    }

    // Simple activity numbers for last N days (for a tiny sparkline)
    public function attemptsLastNDays(): array
    {
        $days = [];
        for ($i = $this->daysRange - 1; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $count = QuizAttempt::whereDate('attempted_at', $day)->count();
            $days[] = $count;
        }
        return $days;
    }

    public function with(): array
    {
        return [
            'stats' => $this->stats(),
            'recentAttempts' => $this->recentAttempts(),
            'latestCertificates' => $this->latestCertificates(),
            'attemptsSpark' => $this->attemptsLastNDays(),
        ];
    }
};
?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 p-6">
    <x-header title="Dashboard" subtitle="Overview & recent activity" separator />

    <!-- Stats cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-card class="p-4 bg-indigo-50 border-l-4 border-indigo-500 shadow hover:shadow-lg transition">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-sm text-indigo-700">Users</div>
                    <div class="text-2xl font-semibold text-indigo-900">{{ $stats['users'] }}</div>
                </div>
                <div class="text-3xl leading-none text-indigo-500">
                    <x-icon name="o-users" />
                </div>
            </div>
            <div class="mt-3 text-xs text-indigo-600">Total registered users</div>
        </x-card>

        <x-card class="p-4 bg-green-50 border-l-4 border-green-500 shadow hover:shadow-lg transition">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-sm text-green-700">Courses</div>
                    <div class="text-2xl font-semibold text-green-900">{{ $stats['courses'] }}</div>
                </div>
                <div class="text-3xl leading-none text-green-500">
                    <x-icon name="o-book-open" />
                </div>
            </div>
            <div class="mt-3 text-xs text-green-600">Active courses</div>
        </x-card>

        <x-card class="p-4 bg-yellow-50 border-l-4 border-yellow-500 shadow hover:shadow-lg transition">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-sm text-yellow-700">Quizzes</div>
                    <div class="text-2xl font-semibold text-yellow-900">{{ $stats['quizzes'] }}</div>
                </div>
                <div class="text-3xl leading-none text-yellow-500">
                    <x-icon name="o-clipboard-document-check" />
                </div>
            </div>
            <div class="mt-3 text-xs text-yellow-600">Total quizzes</div>
        </x-card>

        <x-card class="p-4 bg-red-50 border-l-4 border-red-500 shadow hover:shadow-lg transition">
            <div class="flex justify-between items-start">
                <div>
                    <div class="text-sm text-red-700">Attempts</div>
                    <div class="text-2xl font-semibold text-red-900">{{ $stats['attempts'] }}</div>
                </div>
                <div class="text-3xl leading-none text-red-500">
                    <x-icon name="o-pencil" />
                </div>
            </div>
            <div class="mt-3 text-xs text-red-600">All quiz attempts</div>
        </x-card>
    </div>

    <!-- Two column layout: Recent Attempts | Latest Certificates -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Recent Attempts (wide) -->
        <div class="lg:col-span-2">
            <x-card shadow class="bg-white border border-indigo-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-indigo-700">Recent Quiz Attempts</h3>
                    <div class="text-sm text-gray-500">Last {{ $attemptsSpark ? count($attemptsSpark) : 7 }} days</div>
                </div>

                <div class="mb-4">
                    {{-- tiny inline sparkline using SVG --}}
                    @php
                        $values = $attemptsSpark;
                        $max = max($values) ?: 1;
                        $points = [];
                        $w = 220;
                        $h = 40;
                        $len = count($values);
                        foreach ($values as $i => $v) {
                            $x = ($i / max(1, $len - 1)) * $w;
                            $y = $h - (($v / $max) * $h);
                            $points[] = round($x,1) . ',' . round($y,1);
                        }
                        $pointsStr = implode(' ', $points);
                    @endphp
                    <svg width="100%" viewBox="0 0 {{ $w }} {{ $h }}" class="block text-indigo-500">
                        <polyline fill="none" stroke="currentColor" stroke-width="2" points="{{ $pointsStr }}" />
                    </svg>
                </div>

                <div class="overflow-x-auto">
                    <table class="table w-full border border-gray-200">
                        <thead class="bg-indigo-50 text-indigo-700">
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Quiz</th>
                                <th>Score</th>
                                <th>Attempted At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttempts as $a)
                                <tr class="hover:bg-indigo-50">
                                    <td>{{ $a->id }}</td>
                                    <td>{{ $a->user->name ?? '—' }} <div class="text-xs text-gray-500">{{ $a->user->email ?? '' }}</div></td>
                                    <td>{{ $a->quiz->title ?? '—' }}</td>
                                    <td><span class="font-medium text-red-600">{{ $a->score }}</span></td>
                                    <td>{{ \Illuminate\Support\Carbon::parse($a->attempted_at)->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-400">No attempts yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 flex justify-end">
                    <x-button label="View all attempts" link="{{ route('quiz_attempts') }}" class="btn-outline btn-sm text-indigo-600" />
                </div>
            </x-card>
        </div>

        <!-- Latest Certificates (right column) -->
        <div>
            <x-card shadow class="bg-white border border-green-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-green-700">Latest Certificates</h3>
                    <div class="text-sm text-gray-500">{{ $latestCertificates->count() }} items</div>
                </div>

                <div class="space-y-3">
                    @forelse($latestCertificates as $c)
                        <div class="flex items-center gap-3 p-2 bg-green-50 rounded-lg hover:bg-green-100 transition">
                            <div class="flex-1">
                                <div class="font-medium text-green-900">{{ $c->user->name ?? '—' }}</div>
                                <div class="text-sm text-green-700">{{ $c->course->title ?? '—' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold">{{ \Illuminate\Support\Carbon::parse($c->issued_at)->format('M d, Y') }}</div>
                                <div class="mt-1">
                                    <a href="{{ $c->certificate_url }}" target="_blank" class="text-xs underline text-green-600">View</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-400">No certificates issued yet.</div>
                    @endforelse
                </div>

                <div class="mt-3 flex justify-end">
                    <x-button label="All certificates" link="{{ route('certificates.index') }}" class="btn-outline btn-sm text-green-600" />
                </div>
            </x-card>
        </div>
    </div>

    <!-- Small footer summary -->
    <div class="mt-6">
        <x-card class="p-4 text-sm text-gray-600 bg-indigo-50 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>Quick summary: <strong>{{ $stats['users'] }}</strong> users • <strong>{{ $stats['courses'] }}</strong> courses • <strong>{{ $stats['quizzes'] }}</strong> quizzes</div>
                <div>Updated: {{ \Illuminate\Support\Carbon::now()->format('M d, Y H:i') }}</div>
            </div>
        </x-card>
    </div>
</div>
