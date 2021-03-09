<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
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
     * @return App\Helpers\ResponseFormatter
     */
    public function login(Request $request)
    {

        // Handle exception if something wrong outside login
        try {
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
                return ResponseFormatter::success([
                    'access_token' => $resultToken,
                    'token_type' => 'Bearer',
                    'user' => $user
                ], 'Authenticated');
            }

            // return unauthorized message if authentication failed
            return ResponseFormatter::error([
                'message' => 'Unauthorized'
            ], 'Authentication Failed', 401);
        } catch (Exception $error) {

            // return error if something unexpected happened
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Something went wrong', 500);
        }
    }

    /**
     * Function used to handle register endpoint
     * 
     * @param Illuminate\Http\Request $request
     * @return App\Helpers\ResponseFormatter
     */
    public function register(Request $request)
    {

        try {
            /**
             * Input Validation should be done in client side
             * but just in case let's validate input in server side too
             */
            $validator = Validator::make($request->all(), [
                'email' => 'email|max:255|unique:App\Models\User,email|required',
                'name' => 'string|max:255|required',
                'password' => $this->passwordRules()
            ]);

            // check for the first validation fail, then return error
            if ($validator->fails()) {
                // get all the error
                $errors = $validator->errors()->all();

                // return json with all error
                return ResponseFormatter::error([
                    'message' => 'Invalid Input',
                    'error' => $errors
                ], 'Invalid input', 418);
            }

            // if validation success, create user
            $user = new User;
            $user->name = $validator->name;
            $user->email = $validator->email;
            $user->password = Hash::make($validator->password);
            $user->address = $validator->address;
            $user->house_number = $validator->house_number;
            $user->phone_number = $validator->phone_number;
            $user->city = $validator->city;
            $user->save();

            // create token for newly user
            $resultToken = $user->createToken('api_token')->plainTextToken;

            // return response success
            return ResponseFormatter::success([
                'access_token' => $resultToken,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (Exception $error) {

            // return error if something unexpected happened
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Something went wrong', 500);
        }
    }

    /**
     * Function used to handle logout endpoint
     * 
     * @param Illuminate\Http\Request $request
     * @return App\Helpers\ResponseFormatter
     */
    public function logout(Request $request)
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
     * @return App\Helpers\ResponseFormatter
     */
    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Data Current User has been fetched.');
    }

    /**
     * Function used to handle updateprofile endpoint
     * 
     * @param Illuminate\Http\Request $request
     * @return App\Helpers\ResponseFormatter
     */
    public function updateProfile(Request $request)
    {
        /** 
         * get input data except email, password, and roles
         * changing email, password and roles should be in another endpoint 
         * */
        $input = $request->except(['email', 'password', 'roles']);

        // update user
        User::where('email', $request->user()->email)->update($input);

        $user = Auth::user();

        return ResponseFormatter::success($user, 'Profile Updated !');
    }

    /**
     * Function used to handle updatephoto endpoint
     * 
     * @param Illuminate\Http\Request $request
     * @return App\Helpers\ResponseFormatter
     */
    public function updatePhoto(Request $request)
    {
        // validate the request
        $validator = Validator::make($request->all, [
            'file' => 'image|max:2048|required'
        ]);

        // if validation fails, throw error to client
        if ($validator->fails()) {
            return ResponseFormatter::error([
                'errors' => $validator->errors()
            ], 'Fail to update photo', 400);
        }

        // if validation success
        if ($request->file('file')) {
            $file = $request->file->store(
                'assets/user',
                'public'
            );

            $user = $request->user();
            $user->profile_photo_url = $file;
            $user->save();

            return ResponseFormatter::success([$file], 'Photo profile has been updated.');
        }
    }
}
