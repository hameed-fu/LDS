<?php

use App\Models\Language;
use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use Illuminate\Database\Eloquent\Builder;
use Mary\Traits\Toast;
use Livewire\WithPagination;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public bool $myModal = false;
    public ?int $languageId = null;

    public string $name = '';

    // Computed: List of languages
    public function languages()
    {
        return Language::query()
            ->when(
                $this->search,
                fn (Builder $q) => $q->where('name', 'like', "%$this->search%")
            )
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    // Open create modal
    public function create(): void
    {
        $this->resetForm();
        $this->myModal = true;
    }

    // Edit language
    public function edit(Language $language): void
    {
        $this->languageId = $language->id;
        $this->name = $language->name;
        $this->myModal = true;
    }

    // Save or update language
    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|min:2|max:100',
        ]);

        $language = $this->languageId ? Language::findOrFail($this->languageId) : new Language();
        $language->fill($data)->save();

        $this->resetForm();
        $this->myModal = false;
        $this->success(title: 'Language saved successfully!');
    }

    // Delete language
    public function delete($id): void
    {
        Language::findOrFail($id)->delete();
        $this->warning(title: 'Language deleted!');
    }

    // Reset form fields
    public function resetForm(): void
    {
        $this->reset(['languageId', 'name']);
    }

    public function with(): array
    {
        $headers = [
            ['key' => 'id', 'label' => '#'],
            ['key' => 'name', 'label' => 'Language Name'],
        ];

        return [
            'languages' => $this->languages(),
            'headers'   => $headers,
        ];
    }
};
?>

<div>
    <x-header title="Languages" separator progress-indicator />

    <div class="grid gap-3 sm:flex sm:justify-between mb-4">
        <div class="flex gap-2">
            <x-input placeholder="Search languages..." wire:model.live.debounce="search" icon="o-magnifying-glass" />
            @if ($search)
                <x-button label="Clear" wire:click="$set('search','')" icon="o-x-mark" class="btn-ghost" />
            @endif
        </div>
    </div>

    <x-card class="!p-0 sm:!p-2" shadow>
        <x-table :headers="$headers" :rows="$languages" striped hoverable with-pagination>
            @scope('actions', $lang)
                <div class="flex gap-2 justify-center">
                    <x-button sm icon="o-pencil" class="btn-ghost btn-sm" wire:click="edit({{ $lang->id }})" />
                    <x-button sm icon="o-trash" class="btn-error btn-sm" wire:click="delete({{ $lang->id }})"
                        onclick="return confirm('Are you sure you want to delete this language?')" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="myModal" title="{{ $languageId ? 'Edit Language' : 'Add Language' }}">
        <x-input label="Language Name" wire:model.defer="name" placeholder="Enter language name" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="Save" class="btn-primary" wire:click="save" spinner />
        </x-slot:actions>
    </x-modal>

    <x-button icon="o-plus" class="btn-circle btn-primary btn-lg fixed bottom-6 right-6" @click="$wire.create()" />
</div>
