<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Models\Usuario\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Constants\ConstUsuario\Genero;
use App\Constants\ConstUsuario\EstadoCivil;



class AuthController
{

    
   //Registro de usuario
     
    public function registrar(Request $request){
         //Validar los datos de entrada

        $validator = Validator::make(request()->all(), [
            'municipio_id'           => 'required|exists:municipios,id_municipio',
            'genero'                 => 'nullable|in:' . implode(',', Genero::all()),//llamo a la constante genero para obtener los tipos de genero
            'primer_nombre'          => 'required|string|max:100',
            'segundo_nombre'         => 'nullable|string|max:100',
            'primer_apellido'        => 'required|string|max:50',
            'segundo_apellido'       => 'nullable|string|max:50',
            'fecha_nacimiento'       => 'required|date|before:today',//la fecha de nacimiento no puede ser mayor a la fecha actual
            'estado_civil'           => 'nullable|in:' . implode(',', EstadoCivil::all()),//llamo a la constante estadocivil para obtener los tipos de estado civil
            'email'                  => 'required|string|email|max:100|unique:users',
            'password'               => 'required|string|min:8',
        ]);

        //Si la validación falla, se devuelve un mensaje de error

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        // crear un usuario
        $user = User::create([
            'municipio_id' => $request->input('municipio_id'),
            'genero' => $request->input('genero'),
            'primer_nombre' => $request->input('primer_nombre'),
            'segundo_nombre' => $request->input('segundo_nombre'),
            'primer_apellido' => $request->input('primer_apellido'),
            'segundo_apellido' => $request->input('segundo_apellido'),
            'fecha_nacimiento' => $request->input('fecha_nacimiento'),
            'estado_civil' => $request->input('estado_civil'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')), // Encriptar la contraseña
        ]);


        // Asignar el rol "aspirante" al usuario recién creado
        $user->assignRole('Aspirante');


        //Generar un token para el usuario
        $token = JWTAuth::fromUser($user);



        //Devolver respuesat con el token y el usuario
        return response()->json([
            'menssage'=>'Usuario creado exitosamente',
            // 'user'=>$user,
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
    
        // Devolver respuesta con el token y el usuario
        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            // 'user'    => $user,
            'token'   => $token
        ], 200);
    }

    //actualizar la informacion del usuario
    public function actualizarUsuario(Request $request) {
        // Obtener el usuario autenticado
        $user = JWTAuth::user();

        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'municipio_id'           => 'sometimes|nullable|exists:municipios,id_municipio',
            'genero'                 => 'sometimes|nullable|in:' . implode(',', Genero::all()),
            'primer_nombre'          => 'sometimes|nullable|string|max:100',
            'segundo_nombre'         => 'sometimes|nullable|string|max:100',
            'primer_apellido'        => 'sometimes|nullable|string|max:50',
            'segundo_apellido'       => 'sometimes|nullable|string|max:50',
            'fecha_nacimiento'       => 'sometimes|nullable|date|before:today',
            'estado_civil'           => 'sometimes|nullable|in:' . implode(',', EstadoCivil::all()),
            'email'                  => 'sometimes|nullable|string|email|max:100|unique:users,email,' . $user->id,
        ]);

        // Si la validación falla, devolver mensaje de error
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Actualizar los campos proporcionados en la solicitud
        $user->update($request->only([
            'municipio_id',
            'genero',
            'primer_nombre',
            'segundo_nombre',
            'primer_apellido',
            'segundo_apellido',
            'fecha_nacimiento',
            'estado_civil',
            'email'
        ]));

        // Devolver respuesta con el usuario actualizado
        return response()->json([
            'message' => 'Información actualizada exitosamente',
            'user' => $user
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
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email], // Condición para buscar
            ['token' => $token, 'created_at' => now()] // Datos a actualizar o insertar
        );

        //Generar el enlace de restablecimiento de contraseña
        $resetLink = url('/password/reset', ['token' => $token, 'email' => $user->email]);

        //Enviar un correo electrónico con el token
        Mail::to($user->email)->send(new ResetPasswordMail($user, $resetLink));

        //Devolver respuesta
        return response()->json(['message'=>'Correo electrónico enviado'], 200);
    }

    

















}
