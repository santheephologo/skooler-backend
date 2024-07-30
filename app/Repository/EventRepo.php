<?php

namespace App\Repository;

use Illuminate\Support\Facades\Storage;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Holiday;
use App\Models\Notification;
use App\Models\User;

class EventRepo implements IEventRepo
{
    public function FetchEvents($id)
    {
        $currentDate = now();
        $events = Event::where('school_id', $id)
                        ->reorder('event_datetime', 'desc')
                        ->get();
        $upcomingEvents = Event::where('school_id', $id)
                                ->where('event_datetime', '>', $currentDate)
                                ->orderBy('event_datetime', 'asc')
                                ->get();
        return [$events, $upcomingEvents];
    }
    public function FetchUpcomingEvents($id)
    {
        $currentDate = now();
        $upcomingEvents = Event::where('school_id', $id)
            ->where('event_datetime', '>', $currentDate)
            ->orderBy('event_datetime', 'asc')
            ->get();

        return $upcomingEvents;
    }
    public function AddEvent(Request $request)
    {
        $event = Event::create([
            'school_id' => $request->school_id,
            'event_name' => $request->event_name,
            'event_info' => $request->event_info,
            'venue' => $request->venue,
            'capacity' => $request->capacity,
            'reserved_slots' => 0,
            'payment' => $request->payment,
            'event_datetime' => $request->event_datetime,
            'payment_deadline' => $request->payment_deadline,

        ]);
        return $event ? true : false;
    }
    public function UpdateEvent(Request $request)
    {
        $event = Event::find($request->id);
        $event->update([
            'event_name' => $request->event_name,
            'event_info' => $request->event_info,
            'venue' => $request->venue,
            'payment' => $request->payment,
            'capacity' => $request->capacity,
            'event_datetime' => $request->event_datetime,
            'payment_deadline' => $request->payment_deadline,
        ]);
        return $event ? true : false;
    }
    public function FetchEvent($eventId)
    {
        return Event::find($eventId);
    }
    public function DeleteEvent($eventId)
    {
        $event = Event::where('id', $eventId)->first();
        $event->delete();
        return $event ? true : false;
    }
    public function UploadBookingBankSlip(Request $request)
    {
        $path = $request->file('bankSlip')->store(
            'public/bankslips',
            's3'
        );
        Storage::disk('s3')->setVisibility($path, 'public');

        $booking = Booking::find($request->input('id'));
        if ($booking && $booking->bank_slip) {
            Storage::disk('s3')->delete($booking->bank_slip);
            $booking->bank_slip = $path;
            $booking->save();
            return $path;
        } else {
            return $path;
        }
    }
    public function BookTicket(Request $request, $validatedData)
    {
        $booking = Booking::create($validatedData);
        $event = Event::find((int)$validatedData['event_id']);
        $reserved_slots = $event->reserved_slots;
        if (($event->capacity === $event->reserved_slots) || ($event->capacity < ($event->reserved_slots + (int)($validatedData['tickets'])))) {
            return "Capacity reached.Booking failed";
        } else {
            $reserved_slots = $reserved_slots + (int)$validatedData['tickets'];
            $event->update(['reserved_slots' => $reserved_slots]);
            $school_id = $validatedData['school_id'];
            $name = 'Booking placed success';
            $info = 'You can download your e-reciept from bookings. Thank you';
            $type = 'order';
            $is_read = false;
            $user_id = $request->user_id;

            $notification = new Notification();
            $notification->school_id = $school_id;
            $notification->name = $name;
            $notification->info = $info;
            $notification->type = $type;
            $notification->is_read = $is_read;
            $notification->user_id = $user_id;
            $notification->save();
            return "Booked";
        };
    }
    public function UpdateBookingStatus(Request $request)
    {
        $notification = new Notification();
        $notification->school_id = $validatedData['school_id'];
        $notification->name = 'Update on your recent ticket purchase';
        $notification->type = 'order';
        $notification->is_read  = false;
        $notification->user_id = $request->user_id;
        $booking = Booking::find($request->booking_id);
        if ($booking) {
            if ($request->status === 'Verified') {
                $booking->status = $request->status;
                $booking->save();
                $notification->info = 'Your recent ticket purchase has been approved.Now you can download your E-ticket from bookings';
                $notification->save();
                return true;
            } else {
                $booking->status = $request->status;
                $booking->save();
                $event = Event::find($booking->event_id);
                $reserved = $event->reserved_slots;
                $new = $reserved - (int)($booking->tickets);
                $event->reserved_slots = $new;
                $event->save();
                $notification->info = 'Your recent ticket purchase has been declined. Please contact help centre';
                $notification->save();
                return true;
            }
        } else {
            return false;
        }
    }
    public function RemainingSlots($eventId)
    {
        $event = Event::find($eventId);
        return $event->capacity - $event->reserved_slots;
    }
    public function FetchUserBookings($userId)
    {
        return Booking::where('user_id', $userId)->get();
    }

    public function FetchAllBookings($id)
    {
        $bookings = Booking::where('school_id', $id)
                            ->get();
        foreach ($bookings as $booking) {
            $user = User::find($booking->user_id);
            if ($user) {
                $name = $user->first_name  . " " . $user->last_name;
                $email = $user->email;
                $mobile_no = $user->mobile_no;
                $booking->user_name = $name;
                $booking->user_email = $email;
                $booking->user_mobile_no = $mobile_no;
            }
        }
        return $bookings;
    }
    public function DeleteBooking($bookingId)
    {
        $booking = Booking::find($bookingId);
        if ($booking) {
            $tickets = $booking->tickets;
            $event = Event::find($booking->event_id);
            $reserved = $event->reserved_slots;
            $new = $reserved - (int)($tickets);
            $event->reserved_slots = $new;
            $event->save();
            $booking->delete();
        }

        return $booking ? true : false;
    }

    public function AddHoliday(Request $request)
    {
        $holiday = Holiday::create([
            'school_id' => $request->school_id,
            'name' => $request->name,
            'date' => $request->date,
        ]);
        return $holiday ? true : false;
    }

    public function FetchHolidays($id)
    {

        $holidays = Holiday::where('school_id', $id)
                        ->reorder('created_at', 'desc')
                        ->get();
        return $holidays;
    }

    public function DeleteHoliday($holidayId)
    {
        $holiday = Holiday::findOrFail($holidayId);
        if ($holiday) {
            $holiday->delete();
            return true;
        } else {
            return false;
        }
    }
}
