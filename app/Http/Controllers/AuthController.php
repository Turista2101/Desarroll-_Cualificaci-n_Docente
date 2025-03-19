<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class AuthController
{

    
   //Registro de usuario
     
    public function registrar(Request $request){
         //Validar los datos de entrada

        $validator = Validator::make(request()->all(), [
            'tipo_identificacion'    => 'required|in:CÉDULA DE CIUDADANÍA,CÉDULA DE EXTRANJERIA,NÚMERO IDENTIFICACIÓN PERSONAL,NÚMERO UNICO IDENTIFICACIÓN PERSONAL,PASAPORTE,REGISTRO CIVIL,NÚMERO POR SECRETARIA DE EDUCACIÓN,SERVICIO NACIONAL DE PRUEBAS,TARJETA DE IDENTIDAD,TARJETA PROFESIONAL',
            'numero_identificacion'  => 'required|string|max:50|unique:users',
            'primer_nombre'          => 'required|string|max:100',
            'segundo_nombre'         => 'nullabe|string|max:100',
            'primer_apellido'        => 'required|string|max:50',
            'segundo_apellido'       => 'nullable|string|max:50',
            'email'                  => 'required|string|email|max:100|unique:users',
            'password'               => 'required|string|min:8',
        ]);

        //Si la validación falla, se devuelve un mensaje de error

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // crear un usuario
        $user = user::create([
            'tipo_identificacion' => $request->tipo_identificacion,
            'numero_identificacion' => $request->numero_identificacion,
            'primer_nombre' => $request->primer_nombre,
            'segundo_nombre' => $request->segundo_nombre,
            'primer_apellido' => $request->primer_apellido,
            'segundo_apellido' => $request->segundo_apellido,
            'email'=>$request->email,
            'password'=>Hash::make($request->password), //Encriptar la contraseña
        ]);


        // Asignar el rol "aspirante" al usuario recién creado
        $user->assignRole('Aspirante');


        //Generar un token para el usuario
        $token = JWTAuth::fromUser($user);



        //Devolver respuesat con el token y el usuario
        return response()->json([
            'menssage'=>'Usuario creado exitosamente',
            'user'=>$user,
            'token'=>$token
        ], 201);
    
    }

    //Iniciar sesión
    public function iniciarSesion(Request $request) {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string|min:8',
        ]);
    
        // Si la validación falla, devolver un mensaje de error
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
    
        // Credenciales para autenticar
        $credentials = $request->only('email', 'password');
    
        // Intentar autenticar y generar un token
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }
    
        // Obtener el usuario autenticado
        $user = Auth::user();
    
        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'user'    => $user,
            'token'   => $token
        ], 200);
    }




    //Cerrar sesión

    public function cerrarSesion(){
        //Invalidar el token actual
        JWTAuth::invalidate(JWTAuth::getToken());

        //Devolver respuesta
        return response()->json(['message'=>'Sesión cerrada exitosamente'], 200);
    }




    //Obtener el usuario autenticado

    public function obtenerUsuarioAutenticado(){
        //Obtener el usuario autenticado
        $user = JWTAuth::user();

        //Devolver respuesta con el usuario
        return response()->json(['user'=>$user], 200);
    }




    //Actualizar la contraseña

    public function actualizarContrasena(Request $request){
        //Validar los datos de entrada
        $validator = Validator::make(request()->all(), [
            'password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ]);

        //Si la validación falla, se guarda el mensaje de error
        if($validator->fails()){
            return response()->json(['error'=>$validator->error()], 400);
        }

        //Recuperar el usuario autenticado
        $user = JWTAuth::user();

        //Verificar la contraseña
        if(!Hash::check($request->password, $user->password)){
            return response()->json(['message'=>'Contraseña incorrecta'], 401);
        }

        //Actualizar la contraseña
        $user->password = Hash::make($request->new_password);
        $user->save();

        //Devolver respuesta
        return response()->json(['message'=>'Contraseña actualizada exitosamente'], 200);
    }



    //Restablecer la contraseña
    public function restablecerContrasena(Request $request){
        //Validar los datos de entrada
        $validator = Validator::make(request()->all(), [
            'email' => 'required|string|email|max:100',
        ]);

        //Si la validación falla, se guarda el mensaje de error
        if($validator->fails()){
            return response()->json(['error'=>$validator->error()], 400);
        }

        //Recuperar el usuario por su email
        $user = User::where('email', $request->email)->first();

        //Si el usuario no existe, se guarda el mensaje de error
        if(!$user){
            return response()->json(['message'=>'Usuario no encontrado'], 404);
        }

        //Generar un token para restablecer la contraseña
        $token = bin2hex(random_bytes(32));

        //Guardar el token en la base de datos
        DB::table('password_reset_tokens')->updateOrInsert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        //Enviar un correo electrónico con el token
        Mail::to($request->email)->send(new ResetPasswordMail($token));

        //Devolver respuesta
        return response()->json(['message'=>'Correo electrónico enviado'], 200);
    }


















}
