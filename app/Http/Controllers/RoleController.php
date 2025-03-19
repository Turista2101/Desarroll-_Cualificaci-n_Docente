<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleController
{

    // Obtener todos los roles
    public function listarRoles()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    // Crear un nuevo rol
    public function crearRol(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles',
        ]);

        $rol = Role::create(['name' => $request->name]);

        return response()->json(['message' => 'Rol creado con éxito', 'rol' => $rol]);
    }

    // Asignar un rol a un usuario
    public function asignarRol(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
        ]);

        $usuario = User::findOrFail($request->user_id);
        $usuario->assignRole($request->role);

        return response()->json(['message' => 'Rol asignado con éxito']);
    }

    // Remover un rol de un usuario
    public function removerRol(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
        ]);

        $usuario = User::findOrFail($request->user_id);
        $usuario->removeRole($request->role);

        return response()->json(['message' => 'Rol eliminado con éxito']);
    }

    // Editar un rol (cambiar nombre)
    public function actualizarRol(Request $request, Role $rol, $id)
    {

        $existingRole = Role::find($id);
        if (!$existingRole) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $existingRole->id,
        ]);

        $existingRole->update(['name' => $request->name]);

        return response()->json(['message' => 'Rol actualizado con éxito']);
    }

    // Eliminar un rol
    public function eliminarRol(Role $rol)
    {
        $rol->delete();
        return response()->json(['message' => 'Rol eliminado con éxito']);
    }

    // FUNCIONES PARA PERMISOS

    // Obtener todos los permisos
    public function listarPermisos()
    {
        $permisos = Permission::all();
        return response()->json($permisos);
    }

    // Crear un nuevo permiso
    public function crearPermiso(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions',
        ]);

        $permiso = Permission::create(['name' => $request->name]);

        return response()->json(['message' => 'Permiso creado con éxito', 'permiso' => $permiso]);
    }

    // Asignar un permiso a un rol
    public function asignarPermisoARol(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|exists:permissions,name',
        ]);

        $rol = Role::findOrFail($request->role_id);
        $rol->givePermissionTo($request->permission);

        return response()->json(['message' => 'Permiso asignado al rol con éxito']);
    }

    // Asignar un permiso a un usuario
    public function asignarPermisoAUsuario(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|exists:permissions,name',
        ]);

        $usuario = User::findOrFail($request->user_id);
        $usuario->givePermissionTo($request->permission);

        return response()->json(['message' => 'Permiso asignado al usuario con éxito']);
    }

    // Remover un permiso de un rol
    public function removerPermisoDeRol(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|exists:permissions,name',
        ]);

        $rol = Role::findOrFail($request->role_id);
        $rol->revokePermissionTo($request->permission);

        return response()->json(['message' => 'Permiso eliminado del rol con éxito']);
    }

    // Remover un permiso de un usuario
    public function removerPermisoDeUsuario(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|exists:permissions,name',
        ]);

        $usuario = User::findOrFail($request->user_id);
        $usuario->revokePermissionTo($request->permission);

        return response()->json(['message' => 'Permiso eliminado del usuario con éxito']);
    }
}
