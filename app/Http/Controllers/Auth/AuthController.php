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
use App\Http\Requests\RequestAuth\ActualizarAuthRequest;
use App\Http\Requests\RequestAuth\CrearAuthRequest;
use App\Services\ArchivoService;

class AuthController
{
    protected $archivoService;

    public function __construct(ArchivoService $archivoService)
    {
        $this->archivoService = $archivoService;
    }

    //Registro de usuario

    public function registrar(CrearAuthRequest $request)
    {
        try {
            $user = DB::transaction(function () use ($request) {
                //Validar los datos de entrada
                $datosUser = $request->validated();
                // crear un usuario
                $user = User::create($datosUser);
                // Asignar el rol "aspirante" al usuario recién creado
                $user->assignRole('Aspirante');

                return $user;
            });

            //Generar un token para el usuario
            $token = JWTAuth::fromUser($user);

            //Devolver respuesat con el token y el usuario
            return response()->json([
                'menssage' => 'Usuario creado exitosamente',
                // 'user'=>$user,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el usuario',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    //Iniciar sesión
    public function iniciarSesion(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validator = Validator::make($request->all(), [
                'email'    => 'required|string|email',
                'password' => 'required|string|min:8',
            ]);

            // Si la validación falla, devolver un mensaje de error
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->toJson(), 400);
            }

            // Credenciales para autenticar
            $credentials = $request->only('email', 'password');

            // Intentar autenticar y generar un token
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new \Exception('Credenciales incorrectas', 401);
            }

            // Obtener el usuario autenticado
            $user = Auth::user();

            // Devolver respuesta con el token y el usuario
            return response()->json([
                'message' => 'Inicio de sesión exitoso',
                // 'user'    => $user,
                'token'   => $token
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al iniciar sesión',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }


    //actualizar la informacion del usuario
    public function actualizarUsuario(ActualizarAuthRequest $request)
    {
        try {
            $user = DB::transaction(function () use ($request) {
                // Obtener el usuario autenticado
                $user = JWTAuth::user();

                // Validar los datos de entrada
                $datosAuth = $request->validated();

                // Actualizar los datos del usuario
                $user->update($datosAuth);

                // Manejo del archivo (actualizar o crear documento de identificación)
                if ($request->hasFile('archivo')) {
                    $this->archivoService->actualizarArchivoDocumento(
                        $request->file('archivo'),
                        $user,
                        'Identificacion' // Carpeta para documentos de usuarios
                    );
                }

                return $user->fresh(); // Retornar el usuario actualizado
            });

            // Agregar la URL del archivo si existe
            if ($user->documentosUser) {
                foreach ($user->documentosUser as $documento) {
                    if (!empty($documento->archivo)) {
                        $documento->archivo_url = asset('storage/' . $documento->archivo);
                    }
                }
            }

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'user'    => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el usuario',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    //Cerrar sesión
    public function cerrarSesion()
    {
        try {
            // Invalidar el token actual
            JWTAuth::invalidate(JWTAuth::getToken());

            // Devolver respuesta
            return response()->json(['message' => 'Sesión cerrada exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al cerrar sesión',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    //Obtener el usuario autenticado
    public function obtenerUsuarioAutenticado()
    {
        try {
            // Obtener el usuario autenticado
            $user = JWTAuth::user();

            if (!$user) {
                throw new \Exception('Usuario no autenticado', 401);
            }

            // Agregar la URL del archivo a cada documento si existe
            foreach ($user->documentosUser as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
                }
            }

            // Devolver respuesta con el usuario
            return response()->json(['user' => $user], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener el usuario autenticado',
                'error'   => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }




    //Actualizar la contraseña

    public function actualizarContrasena(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:8',
                'new_password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                throw new \Exception('error', 400);
            }

            // Obtener el usuario autenticado
            $user = JWTAuth::user();

            // Verificar la contraseña actual
            if (!Hash::check($request->password, $user->password)) {
                throw new \Exception('Contraseña incorrecta', 401);
            }

            // Actualizar la contraseña
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json(['message' => 'Contraseña actualizada exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la contraseña.',
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }



    //Restablecer la contraseña
    public function restablecerContrasena(Request $request)
    {
        //Validar los datos de entrada
        $validator = Validator::make(request()->all(), [
            'email' => 'required|string|email|max:100',
        ]);

        //Si la validación falla, se guarda el mensaje de error
        if ($validator->fails()) {
            return response()->json(['error' => $validator->error()], 400);
        }

        //Recuperar el usuario por su email
        $user = User::where('email', $request->email)->first();

        //Si el usuario no existe, se guarda el mensaje de error
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
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
        return response()->json(['message' => 'Correo electrónico enviado'], 200);
    }
}
