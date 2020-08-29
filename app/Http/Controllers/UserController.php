<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'gender' => [
                'required',
                Rule::in(['male', 'female']),
            ],
            'phone' => 'required|min:4',
            'type' => [
                'required',
                Rule::in(['student', 'admin']),
            ],
        ]);

        try {
            $user = new User;

            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->gender = $request->gender;
            $user->phone = $request->phone;
            $user->type = $request->type;
            $user->status = 1; // default active

            if($user->save()){
                $code = 200;
                $output = [
                    'user' => $user,
                    'code' => $code,
                    'message' => 'User created successfully.'
                ];
            } else {
                $code = 500;
                $output = [
                    'code' => $code,
                    'message' => 'An error occured while creating user.'
                ];
            }
        } catch(Exception $e){
            $code =  500;
            $output = [
                'code' => $code,
                'message' => 'An error occured while creating user.'
            ];
        }

       return response()->json($output, $code);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->validate($request, [
            'name' => 'required|min:2',
            'password' => 'required|min:6',
            'gender' => [
                'required',
                Rule::in(['male', 'female']),
            ],
            'phone' => 'required|min:4',
            'type' => [
                'required',
                Rule::in(['student', 'admin']),
            ],
            'status' => [
                'required',
                Rule::in([1, 0]),
            ],
        ]);


        try {
            $user->name = $request->name;
            $user->password = Hash::make($request->password);
            $user->gender = $request->gender;
            $user->phone = $request->phone;
            $user->type = $request->type;
            $user->status = $request->status;

            if($user->save()){
                $code = 200;
                $output = [
                    'user' => $user,
                    'code' => $code,
                    'message' => 'User updated successfully.'
                ];
            } else {
                $code = 500;
                $output = [
                    'code' => $code,
                    'message' => 'An error occured while creating user.'
                ];
            }
        } catch(Exception $e){
            $code =  500;
            $output = [
                'code' => $code,
                'message' => 'An error occured while creating user.'
            ];
        }

       return response()->json($output, $code);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json('Resource removed successfully');
    }

    public function login(Request $request)
    {
    
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $input = $request->only('email','password');

        if(!$authorized = Auth::attempt($input)){
            $code =  401;
            $output = [
                'code' => $code,
                'message' => 'User is not authorized.'
            ];
        } else {
            $token = $this->respondWithToken($authorized);
            $code = 201;
            $output = [
                'code' => $code,
                'message' => 'User logged in successfully.',
                'token' => $token
            ];
        }

        return response()->json($output, $code);
    }
}
