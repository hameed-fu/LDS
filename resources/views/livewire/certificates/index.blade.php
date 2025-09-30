<?php

use Livewire\Volt\Component;
use App\Models\Certificate;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function certificates()
    {
        return Certificate::with(['user', 'course'])
            ->latest('issued_at')
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'certificates' => $this->certificates(),
        ];
    }
};

?>
<div>
    <x-header title="Certificates" separator />

    <x-card shadow>
        <table class="table w-full">
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Course</th>
                    <th>Certificate</th>
                    <th>Issued At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($certificates as $cert)
                    <tr>
                        <td>{{ $cert->id }}</td>
                        <td>{{ $cert->user->name ?? 'N/A' }}</td>
                        <td>{{ $cert->course->title ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ $cert->certificate_url }}" target="_blank" class="text-blue-600 underline">
                                View Certificate
                            </a>
                        </td>
                        <td>{{ $cert->issued_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="p-2">
            {{ $certificates->links() }}
        </div>
    </x-card>
</div>
