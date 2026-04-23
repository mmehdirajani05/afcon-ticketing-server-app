<?php

namespace App\Services\Ticket;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;

class DigitalTicketService
{
    /**
     * Generate a PDF ticket for the given booking.
     * Returns the PDF binary content.
     */
    public function generatePdf(Booking $booking): string
    {
        $qrCodeDataUri = $this->generateQrCode($booking);

        $pdf = Pdf::loadView('tickets.digital', [
            'booking'       => $booking,
            'user'          => $booking->user,
            'qrCodeDataUri' => $qrCodeDataUri,
            'generatedAt'   => now()->format('d M Y H:i') . ' UTC',
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    /**
     * Stream a ticket PDF directly to the browser.
     */
    public function stream(Booking $booking): \Illuminate\Http\Response
    {
        $qrCodeDataUri = $this->generateQrCode($booking);

        $pdf = Pdf::loadView('tickets.digital', [
            'booking'       => $booking,
            'user'          => $booking->user,
            'qrCodeDataUri' => $qrCodeDataUri,
            'generatedAt'   => now()->format('d M Y H:i') . ' UTC',
        ])->setPaper('a4', 'portrait');

        $filename = 'AFCON2027-Ticket-' . $booking->id . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Build QR code data URI for embedding in PDF.
     * The QR payload includes fan_id + caf_ticket_ref for venue scanning.
     */
    private function generateQrCode(Booking $booking): string
    {
        $qrPayload = json_encode([
            'ref'      => $booking->caf_ticket_ref,
            'fan_id'   => $booking->fan_id,
            'match'    => $booking->match_id,
            'category' => $booking->ticket_category,
            'seat'     => $booking->seat_info,
        ]);

        try {
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($qrPayload)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(300)
                ->margin(10)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->build();

            return $result->getDataUri();
        } catch (\Throwable $e) {
            Log::error('QR code generation failed', ['booking' => $booking->id, 'error' => $e->getMessage()]);

            // Return a placeholder data URI so PDF still renders
            return 'data:image/png;base64,' . base64_encode('QR_ERROR');
        }
    }
}
