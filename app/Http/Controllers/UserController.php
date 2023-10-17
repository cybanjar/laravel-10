<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\PasswordValidationRules;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as RulesPassword;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
  use PasswordValidationRules;

  public function login(Request $request)
  {
    try {
      // Validasi input
      $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required']
      ]);

      // cek credential login
      $credential = request(['email', 'password']);
      if (!Auth::attempt($credential)) {
        return ResponseFormatter::error([
          'message' => 'Unauthorized'
        ], 'Authentication failed', 500);
      }

      // jika hash tidak sesuai beri error
      $user = User::where('email', $request->email)->first();
      if (!Hash::check($request->password, $user->password, [])) {
        throw new \Exception('Invalid Credential');
      }

      // jika berhasil maka loginkan dengan token
      $tokenResult = $user->createToken('authToken')->plainTextToken;
      return ResponseFormatter::success([
        'access_token' => $tokenResult,
        'token_type' => 'Bearer',
        'user' => $user
      ], 'Authenticated');
    } catch (Exception $error) {
      return ResponseFormatter::error([
        'message' => 'Something went wrong',
        'error' => $error
      ], 'Authentication Failed', 500);
    }
  }

  public function register(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'name'      => 'required|string',
        'email'     => 'required|string|email|unique:users',
        'password' => 'required|min:6'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'error' => $validator->errors(),
          'message' => 'Register Failed!'
        ], 401);
      }


      $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
      ]);

      $tokenResult = $user->createToken('authToken')->plainTextToken;

      return ResponseFormatter::success([
        'access_token' => $tokenResult,
        'token_type' => 'Bearer',
        'user' => $user
      ], 'Authenticated');
    } catch (Exception $error) {
      return ResponseFormatter::error([
        'message' => 'Something went wrong',
        'error' => $error
      ], 'Registration Failed', 500);
    }
  }

  public function logout(Request $request)
  {
    try {
      $request->user()->tokens()->delete();

      return response()->json([
        'success' => true,
        'message' => 'Logout Success!',
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Logout Failed: ' . $e->getMessage(),
      ]);
    }
  }

  public function fetch(Request $request)
  {
    return ResponseFormatter::success($request->user(), 'Data berhasil diambil');
  }

  public function updateProfile(Request $request)
  {
    try {
      // ambil semua data
      $data = $request->all();

      // user yg sedang login
      $user = Auth::user();
      $user->update($data);

      return ResponseFormatter::success($user, 'Profile Updated!');
    } catch (Exception $error) {
      return ResponseFormatter::error([
        'message' => 'Check again',
        'error' => $error
      ], 'Failed', 500);
    }

  }

  public function updatePhoto(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'file' => 'required|image|max:2048'
    ]);

    if ($validator->fails()) {
      return ResponseFormatter::error(
        ['error' => $validator->errors()],
        'Failed update photo!',
        401
      );
    }

    // cek pastikan fotonya ada
    if ($request->file('file')) {
      $file = $request->file->store('assets/user', 'public');

      // simpan foto ke db (url)
      $user = Auth::user();
      $user->profile_photo_path = $file;
      $user->update();

      return ResponseFormatter::success([$file], 'Successfully!');
    }
  }
}
