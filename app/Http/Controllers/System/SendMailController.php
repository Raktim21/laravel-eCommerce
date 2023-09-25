<?php

namespace App\Http\Controllers\System;

use Exception;
use App\Models\Order;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Services\OrderService;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\GeneralSettingService;

class SendMailController extends Controller
{
    /**
     * @throws Exception
     */
    public function sendInvoice($order_id)
    {
        try {
            $attachmentContent = $this->getAttachmentContent($order_id);

            $order = Order::with('user')->find($order_id);

            $to = $order->user->username;

            $mail_body = 'Dear Customer, ' . PHP_EOL . '
        Your order will be delivered soon. Please find the invoice attached to this mail.' . PHP_EOL . '
        Regards, ' . PHP_EOL . 'Selopia Ecommerce Team';

            Mail::raw($mail_body, function ($msg) use ($attachmentContent, $to) {
                $msg->to($to)
                    ->subject('Order Delivery')
                    ->attachData($attachmentContent, 'invoice.pdf', [
                        'mime' => 'application/pdf',
                    ]);
            });

            return response()->json([
                'status' => true
            ]);
        } catch (\Throwable $th)
        {
            return response()->json([
                'status'  => false,
                'errors'  => ['No email server is configured.']
            ], 400);
        }
    }


    public function sendReply(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        if($contact->reply_from_merchant)
        {
            return response()->json([
                'status' => false,
                'errors' => ['Reply has already been sent to this customer.']
            ], 400);
        }

        $validator = Validator::make($request->all(),[
            'subject' => 'required|string|min:3',
            'body' => 'required|string|min:5',
            'attachment' => 'nullable|file|mimes:pdf,png,jpg,jpeg'
        ]);

        if($validator->fails())
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        $recipient  = $contact->email;
        $subject    = $request->subject;
        $body       = $request->body;
        $attachment = request()->file('attachment');

        try {
            Mail::raw($body, function ($msg) use ($recipient, $subject, $attachment) {

                $msg->to($recipient)->subject($subject);

                if ($attachment) {
                    $msg->attach($attachment->getRealPath(), [
                        'as' => $attachment->getClientOriginalName(),
                        'mime' => $attachment->getClientMimeType()
                    ]);
                }
            });

        } catch (\Throwable $th) {}

        $contact->update([
            'reply_from_merchant'   => $request->body
        ]);

        return response()->json([
            'status' => true,
        ]);
    }


    /**
     * @throws Exception
     */
    private function getAttachmentContent($order_id)
    {
        $order = (new OrderService(new Order()))->getData($order_id);

        $general = (new GeneralSettingService(new GeneralSetting()))->getSetting();

        $data = array(
            'order' => $order,
            'general' => $general,
            'title' => $order->order_number
        );

        $pdf = PDF::loadView('invoice', $data);

        $pdf->render();

        return $pdf->output();
    }
}
