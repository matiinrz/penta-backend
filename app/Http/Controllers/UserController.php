<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User resource",
 *     @OA\Property(property="id", type="integer", description="User ID"),
 *     @OA\Property(property="name", type="string", description="Name of the user"),
 *     @OA\Property(property="email", type="string", format="email", description="User email address"),
 *     @OA\Property(property="phone", type="string", description="User phone number"),
 *     @OA\Property(property="permission_group", type="string", enum={"normal", "moderator", "admin", "superadmin", "developer"}, description="Permission group of the user"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the user was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the user was last updated"),
 * )
 */

class UserController extends Controller
{
    /**
     * Get all users.
     *
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get list of users",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index()
    {
        $users = User::paginate(10); // Paginate users
        return response()->json($users);
    }

    /**
     * Get a single user.
     *
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get a user by ID",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Create a new user.
     *
     * @OA\Post(
     *     path="/api/users",
     *     summary="Create a user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "phone", "permission_group"},
     *             @OA\Property(property="name", type="string", description="Name of the user"),
     *             @OA\Property(property="email", type="string", format="email", description="User email address"),
     *             @OA\Property(property="phone", type="string", description="User phone number"),
     *             @OA\Property(property="password", type="string", format="password", description="Password for the user"),
     *             @OA\Property(property="permission_group", type="string", enum={"normal", "moderator", "admin", "superadmin", "developer"}, description="Permission group of the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'phone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/|unique:users',
            'password' => 'required|string|min:6',
            'permission_group' => 'required|in:normal,moderator,admin,superadmin,developer',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'permission_group' => $validated['permission_group'],
        ]);

        return response()->json($user, 201);
    }

    /**
     * Update an existing user.
     *
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update a user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", description="Name of the user"),
     *             @OA\Property(property="email", type="string", format="email", description="User email address"),
     *             @OA\Property(property="phone", type="string", description="User phone number"),
     *             @OA\Property(property="permission_group", type="string", enum={"normal", "moderator", "admin", "superadmin", "developer"}, description="Permission group of the user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|regex:/^\+?[1-9]\d{1,14}$/|unique:users,phone,' . $id,
            'password' => 'sometimes|string|min:6',
            'permission_group' => 'sometimes|in:normal,moderator,admin,superadmin,developer',
        ]);

        $user->update(array_merge(
            $validated,
            isset($validated['password']) ? ['password' => Hash::make($validated['password'])] : []
        ));

        return response()->json($user);
    }

    /**
     * Delete a user.
     *
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(response=404, description="User not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
