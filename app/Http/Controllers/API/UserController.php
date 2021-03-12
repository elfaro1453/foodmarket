<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Actions\Fortify\PasswordValidationRules;

class UserController extends Controller
{
    use PasswordValidationRules;

    /**
     * Function used to handle login endpoint
     *
     * @param Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function login(Request $request) : JsonResponse
    {
        /**
         * Get a portion of the request
         *
         * @see https://laravel.com/docs/8.x/requests#retrieving-a-portion-of-the-input-data
         */
        $credential = $request->only(['email', 'password']);

        // check if authentication success
        if (Auth::attempt($credential)) {
            // get the user
            $user = User::where('email', $request->email)->first();

            /**
             * Create Token for User using Laravel Sanctum
             *
             * @see https://laravel.com/docs/8.x/sanctum#api-token-authentication
             * */
            $resultToken = $user->createToken('api_token')->plainTextToken;

            // return response success
            return ResponseFormatter::success(
                [
                'access_token' => $resultToken,
                'token_type' => 'Bearer',
                'user' => $user
                ],
                'Authenticated'
            );
        }

        // return unauthorized message if authentication failed
        return ResponseFormatter::error(
            ['message' => 'Unauthorized'],
            'Authentication Failed',
            401
        );
    }

    /**
     * Function used to handle register endpoint
     *
     * @param Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function register(Request $request) : JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
            'email' => 'email|max:255|unique:App\Models\User,email|required',
            'name' => 'string|max:255|required',
            'password' => $this->passwordRules()
            ]
        );

        // check for the first validation fail, then return error
        if ($validator->fails()) {
            // get all the error
            $errors = $validator->errors()->all();

            // return json with all error
            return ResponseFormatter::error(
                [
                'message' => 'Invalid Input',
                'error' => $errors
                ],
                'Invalid input',
                400
            );
        }

        // if validation success, create user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->address = $request->address;
        $user->house_number = $request->house_number;
        $user->phone_number = $request->phone_number;
        $user->city = $request->city;
        $user->save();

        // create token for newly user
        $resultToken = $user->createToken('api_token')->plainTextToken;

        // return response success
        return ResponseFormatter::success(
            [
            'access_token' => $resultToken,
            'token_type' => 'Bearer',
            'user' => $user
            ],
            'Authenticated'
        );
    }

    /**
     * Function used to handle logout endpoint
     *
     * @param Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function logout(Request $request) : JsonResponse
    {
        /**
         * @see https://laravel.com/docs/8.x/sanctum#revoking-tokens
         */
        $isTokenDeleted = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($isTokenDeleted, 'Token Revoked');
    }

    /**
     * Function used to handle fetch current user endpoint
     *
     * @param Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function fetch(Request $request) : JsonResponse
    {
        return ResponseFormatter::success($request->user(), 'Data Current User has been fetched.');
    }

    /**
     * Function used to handle updateprofile endpoint
     *
     * @param Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request) : JsonResponse
    {
        /**
         * get input data except email, password, and roles
         * changing email, password and roles should be in another endpoint
         * */
        $input = $request->except(['email', 'password', 'roles']);

        // update user
        User::where('email', $request->user()->email)->update($input);

        $user = Auth::user();

        return ResponseFormatter::success($user, 'Profile berhasil diperbarui !');
    }

    /**
     * Function used to handle updatephoto endpoint
     *
     * @param Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function updatePhoto(Request $request) : JsonResponse
    {
        // validate the request
        $validator = Validator::make(
            $request->all(),
            ['file' => 'image|max:2048|required']
        );

        // if validation fails, throw error to client
        if ($validator->fails()) {
            return ResponseFormatter::error(
                ['errors' => $validator->errors()],
                'Gagal mengupdate foto',
                400
            );
        }

        // if validation success
        if ($request->file('file')) {
            $file = $request->file->store(
                'assets/user',
                'public'
            );

            $user = $request->user();
            $user->profile_photo_path = $file;
            $user->save();

            return ResponseFormatter::success([$file], 'Foto profil berhasil diupdate.');
        }
    }
}
