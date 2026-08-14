@php($editing = isset($room))
<form method="POST" action="{{ $formAction }}">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="room_type_id">Room type</label><select class="form-select @error('room_type_id') is-invalid @enderror" id="room_type_id" name="room_type_id" required><option value="">Select a room type</option>@foreach($roomTypes as $roomType)<option value="{{ $roomType->room_type_id }}" @selected((string) old('room_type_id', $editing ? $room->room_type_id : '') === (string) $roomType->room_type_id)>{{ $roomType->tourismService->service_name }}</option>@endforeach</select>@error('room_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-3"><label class="form-label" for="room_number">Room number</label><input class="form-control @error('room_number') is-invalid @enderror" id="room_number" name="room_number" value="{{ old('room_number', $editing ? $room->room_number : '') }}" required>@error('room_number')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-3"><label class="form-label" for="status">Status</label><select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>@foreach(['active', 'inactive'] as $status)<option value="{{ $status }}" @selected(old('status', $editing ? $room->status : 'active') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    </div>
    <div class="mt-4 d-flex gap-2"><button class="btn btn-primary" type="submit">{{ $submitLabel }}</button><a class="btn btn-outline-secondary" href="{{ route('hotel.rooms.index') }}">Cancel</a></div>
</form>
