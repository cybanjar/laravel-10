<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\MyTestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailController extends Controller
{
  public function index(){
    try {
      $details = [
        'title' => 'Mail from website nyobaaa..',
        'body' => 'This is testing mail'
      ];

      Mail::to('ahmadsyamsul@yopmail.com')->send(new MyTestMail($details));

      Log::info("Email sudah terkirim.");

      return "Email Sudah Terkirim";
    } catch (\Exception $e) {
      Log::error('Error sending email: ' . $e->getMessage());

      return "Terjadi kesalahan saat mengirim email. Silakan coba lagi nanti.";
    }
  }
}
