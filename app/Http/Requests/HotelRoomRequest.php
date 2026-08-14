<?php

namespace App\Http\Requests;

use App\Models\HotelRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HotelRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $room = $this->route('hotelRoom');

        if (! $this->user()?->serviceProvider || $this->user()->serviceProvider->provider_type !== 'hotel') {
            return false;
        }

        return ! $room instanceof HotelRoom
            || (int) $room->hotelRoomType?->tourismService?->provider_id === (int) $this->user()->serviceProvider->provider_id;
    }

    public function rules(): array
    {
        return [
            'room_type_id' => ['required', 'integer', Rule::exists('hotel_room_types', 'room_type_id')],
            'room_number' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(HotelRoom::STATUSES)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->user()?->serviceProvider || ! $this->filled('room_number')) {
                return;
            }

            $room = $this->route('hotelRoom');
            $roomNumber = trim((string) $this->input('room_number'));
            $query = HotelRoom::query()
                ->where('room_number', $roomNumber)
                ->whereHas('hotelRoomType.tourismService', fn ($query) => $query->where('provider_id', $this->user()->serviceProvider->provider_id));

            if ($room instanceof HotelRoom) {
                $query->whereKeyNot($room->room_id);
            }

            if ($roomNumber !== '' && $query->exists()) {
                $validator->errors()->add('room_number', 'This room number is already used by your hotel.');
            }
        });
    }
}
