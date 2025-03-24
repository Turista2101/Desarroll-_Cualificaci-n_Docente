<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Usuario\User;

class UserController
{

    //Listar todos los usuarios
    public function listarUsuarios()
    {
        //Obtener todos los usuarios
        $users = User::all();

        //Devolver respuesta con los usuarios
        return response()->json($users, 200);
    }
    //Editar un usuario
    public function editarUsuario(Request $request,$id){

        //Buscar el usuario por id
        $user = User::find($id);

        //Si el usuario no existe, devolver un mensaje de error
        if(!$user){
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        //Actualizar el usuario
        $user->update($request->all());

        //Devolver respuesta con el usuario actualizado
        return response()->json($user, 200);
    }

    //Eliminar un usuario
    public function eliminarUsuario($id){

        //Buscar el usuario por id
        $user = User::find($id);

        //Si el usuario no existe, devolver un mensaje de error
        if(!$user){
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        //Eliminar el usuario
        $user->delete();

        //Devolver respuesta con el usuario eliminado
        return response()->json(['message' => 'Usuario eliminado'], 200);
    }
}