<?php

namespace App\Repository;

use Illuminate\Http\Request;

interface IEventRepo
{
    public function FetchEvents($id);
    public function FetchUpcomingEvents($id);
    public function AddEvent(Request $request);
    public function UpdateEvent(Request $request);
    public function FetchEvent($eventId);
    public function DeleteEvent($eventId);
    public function UploadBookingBankSlip(Request $request);
    public function BookTicket(Request $request, $validatedData);
    public function UpdateBookingStatus(Request $request);
    public function FetchUserBookings($userId);
    public function RemainingSlots($eventId);
    public function FetchAllBookings($id);
    public function DeleteBooking($bookingId);
    public function AddHoliday(Request $request);
    public function FetchHolidays($id);
    public function DeleteHoliday($holidayId);
}
