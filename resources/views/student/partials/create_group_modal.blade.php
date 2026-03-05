<div class="modal fade" id="createGroupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Create Study Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('student.groups.create') }}" method="POST">
                @csrf

                <div class="modal-body">
<div class="mb-3">
    <label class="form-label">Class</label>

    <select name="class_id" class="form-control" required>
        <option value="">Select Class</option>

        @foreach($classes as $class)
            <option value="{{ $class->id }}">
                {{ $class->name }}
            </option>
        @endforeach

    </select>
</div>
                    <div class="mb-3">
                        <label class="form-label">Group Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Max Members</label>
                        <input type="number"
                               name="max_members"
                               class="form-control"
                               value="20"
                               min="2"
                               max="100">
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button class="btn btn-primary">
                        Create Group
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>