<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelRoomRequest;
use App\Models\HotelRoom;
use App\Models\HotelRoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelRoomController extends Controller
{
    public function index(Request $request): View
    {
        $rooms = HotelRoom::query()
            ->with('hotelRoomType.tourismService')
            ->whereHas('hotelRoomType.tourismService', fn ($query) => $query->where('provider_id', $request->user()->serviceProvider->provider_id))
            ->orderBy('room_number')
            ->get();

        return view('hotel.rooms.index', compact('rooms'));
    }

    public function create(Request $request): View
    {
        return view('hotel.rooms.create', ['roomTypes' => $this->roomTypes($request)]);
    }

    public function store(HotelRoomRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $roomType = $this->ownedRoomType($request, (int) $data['room_type_id']);
        $roomType->hotelRooms()->create([
            'room_number' => $data['room_number'],
            'status' => $data['status'],
        ]);

        return to_route('hotel.rooms.index')->with('success', 'Physical room added.');
    }

    public function edit(Request $request, HotelRoom $hotelRoom): View
    {
        $this->ensureOwned($request, $hotelRoom);

        return view('hotel.rooms.edit', [
            'room' => $hotelRoom->load('hotelRoomType'),
            'roomTypes' => $this->roomTypes($request),
        ]);
    }

    public function update(HotelRoomRequest $request, HotelRoom $hotelRoom): RedirectResponse
    {
        $this->ensureOwned($request, $hotelRoom);
        $data = $request->validated();
        $this->ownedRoomType($request, (int) $data['room_type_id']);
        $hotelRoom->update([
            'room_type_id' => $data['room_type_id'],
            'room_number' => $data['room_number'],
            'status' => $data['status'],
        ]);

        return to_route('hotel.rooms.index')->with('success', 'Physical room updated.');
    }

    public function destroy(Request $request, HotelRoom $hotelRoom): RedirectResponse
    {
        $this->ensureOwned($request, $hotelRoom);

        if ($hotelRoom->hotelRoomReservations()->exists()) {
            return back()->with('error', 'This room has reservation history and cannot be deleted. Mark it inactive instead.');
        }

        $hotelRoom->delete();

        return to_route('hotel.rooms.index')->with('success', 'Physical room removed.');
    }

    private function roomTypes(Request $request)
    {
        return HotelRoomType::query()
            ->with('tourismService')
            ->whereHas('tourismService', fn ($query) => $query->where('provider_id', $request->user()->serviceProvider->provider_id))
            ->orderBy('room_type_id')
            ->get();
    }

    private function ownedRoomType(Request $request, int $roomTypeId): HotelRoomType
    {
        return HotelRoomType::whereKey($roomTypeId)
            ->whereHas('tourismService', fn ($query) => $query->where('provider_id', $request->user()->serviceProvider->provider_id))
            ->firstOrFail();
    }

    private function ensureOwned(Request $request, HotelRoom $room): void
    {
        abort_unless(
            (int) $room->hotelRoomType()
                ->whereHas('tourismService', fn ($query) => $query->where('provider_id', $request->user()->serviceProvider->provider_id))
                ->value('room_type_id') === (int) $room->room_type_id,
            403
        );
    }
}
