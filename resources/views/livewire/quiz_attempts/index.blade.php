<?php

use Livewire\Volt\Component;
use App\Models\QuizAttempt;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function attempts()
    {
        return QuizAttempt::with(['quiz', 'user'])
            ->latest('attempted_at')
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'attempts' => $this->attempts(),
        ];
    }
};

?>
<div>
    <x-header title="Quiz Attempts" separator />

    <x-card shadow>
        <table class="table w-full">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Quiz</th>
                    <th>Score</th>
                    <th>Attempted At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attempts as $attempt)
                    <tr>
                        <td>{{ $attempt->id }}</td>
                        <td>{{ $attempt->user->name ?? 'N/A' }}</td>
                        <td>{{ $attempt->quiz->title ?? 'N/A' }}</td>
                        <td>{{ $attempt->score }}</td>
                        <td>{{ $attempt->attempted_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="p-2">
            {{ $attempts->links() }}
        </div>
    </x-card>
</div>
