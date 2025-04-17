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
use App\Models\Aspirante\Documento;
use Illuminate\Support\Facades\Storage;

class AuthController
{

    /**
     * Registrar un nuevo usuario.
     *
     * @OA\Post(
     *     path="/auth/registrar-usuario",
     *     tags={"Auth"},
     *     summary="Registrar usuario",
     *     description="Registra un nuevo usuario y devuelve un token de autenticación. Requiere datos válidos.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
 *                  required={"municipio_id", "tipo_identificacion", "numero_identificacion", "primer_nombre", "primer_apellido", "fecha_nacimiento", "email", "password"},
 *                  @OA\Property(property="municipio_id", type="integer", example=1, description="ID del municipio"),
 *                  @OA\Property(property="tipo_identificacion", type="string", enum={"Cédula de ciudadanía", "Cédula de extranjería", "Número único de identificación personal", "Pasaporte", "Registro civil", "Número por secretaría de educación", "Servicio nacional de pruebas", "Tarjeta de identidad", "Tarjeta profesional"}, example="Cédula de ciudadanía"),
 *                  @OA\Property(property="numero_identificacion", type="string", example="123456789", description="Número de identificación único"),
 *                  @OA\Property(property="genero", type="string", enum={"Masculino", "Femenino", "Otro"}, nullable=true, example="Masculino"),
 *                  @OA\Property(property="primer_nombre", type="string", example="Juan"),
 *                  @OA\Property(property="segundo_nombre", type="string", nullable=true, example="Carlos"),
 *                  @OA\Property(property="primer_apellido", type="string", example="Pérez"),
 *                  @OA\Property(property="segundo_apellido", type="string", nullable=true, example="Gómez"),
 *                  @OA\Property(property="fecha_nacimiento", type="string", format="date", example="1990-01-01"),
 *                  @OA\Property(property="estado_civil", type="string", enum={"Soltero", "Casado", "Divorciado", "Viudo"}, nullable=true, example="Soltero"),
 *                  @OA\Property(property="email", type="string", format="email", example="juan.perez@example.com"),
 *                  @OA\Property(property="password", type="string", format="password", example="password123"),
 *                  @OA\Property(property="password_confirmation", type="string", format="password", example="password123")
 *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario creado exitosamente"),
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al crear el usuario"),
     *             @OA\Property(property="error", type="string", example="Detalles del error...")
     *         )
     *     )
     * )
     */
    public function registrar(CrearAuthRequest $request)
    {
        try{
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
        
        }catch (\Exception $e){
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
            ],$e->getCode() ?: 500);
        }
    }
        /**
     * Actualizar la información del usuario autenticado.
     *
     * @OA\Post(
     *     path="/auth/actualizar-usuario",
     *     tags={"Auth"},
     *     summary="Actualizar usuario",
     *     description="Actualiza la información del usuario autenticado.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"municipio_id", "tipo_identificacion", "numero_identificacion", "primer_nombre", "primer_apellido", "fecha_nacimiento", "email"},
     *             @OA\Property(property="municipio_id", type="integer", example=1),
     *             @OA\Property(property="tipo_identificacion", type="string", enum={"Cédula de ciudadanía", "Cédula de extranjería", "Tarjeta profesional"}, example="Tarjeta profesional"),
     *             @OA\Property(property="numero_identificacion", type="string", example="123456789"),
     *             @OA\Property(property="genero", type="string", enum={"Masculino", "Femenino", "Otro"}, nullable=true, example="Masculino"),
     *             @OA\Property(property="primer_nombre", type="string", example="Juan"),
     *             @OA\Property(property="segundo_nombre", type="string", nullable=true, example="Carlos"),
     *             @OA\Property(property="primer_apellido", type="string", example="Pérez"),
     *             @OA\Property(property="segundo_apellido", type="string", nullable=true, example="Gómez"),
     *             @OA\Property(property="fecha_nacimiento", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="estado_civil", type="string", enum={"Soltero", "Casado", "Divorciado"}, nullable=true, example="Soltero"),
     *             @OA\Property(property="email", type="string", format="email", example="juan.perez@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario actualizado exitosamente"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="municipio_id", type="integer", example=1),
     *                 @OA\Property(property="tipo_identificacion", type="string", example="Tarjeta profesional"),
     *                 @OA\Property(property="numero_identificacion", type="string", example="123456789"),
     *                 @OA\Property(property="primer_nombre", type="string", example="Juan"),
     *                 @OA\Property(property="primer_apellido", type="string", example="Pérez"),
     *                 @OA\Property(property="email", type="string", example="juan.perez@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al actualizar el usuario"),
     *             @OA\Property(property="error", type="string", example="Detalles del error...")
     *         )
     *     )
     * )
     */

    //actualizar la informacion del usuario
    public function actualizarUsuario( ActualizarAuthRequest $request)
    {
        try {
            $user = DB::transaction(function () use ($request) {
                // Obtener el usuario autenticado
                $user = JWTAuth::user();
    
                // Validar los datos de entrada
                $datosAuth = $request->validated();
    
                // Actualizar el usuario
                $user->update($datosAuth);
    
                // Manejo del archivo
                if ($request->hasFile('archivo')) {
                    $archivo = $request->file('archivo');
                    $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
                    $rutaArchivo = $archivo->storeAs('documentos/Identificacion', $nombreArchivo, 'public');
    
                    // Buscar el documento asociado
                    $documento = Documento::where('documentable_id', $user->id)
                        ->where('documentable_type', User::class)
                        ->where('user_id', $user->id)
                        ->first();
    
                    if ($documento) {
                        // Eliminar archivo anterior si existe
                        Storage::disk('public')->delete($documento->archivo);
                        $documento->update([
                            'archivo' => str_replace('public/', '', $rutaArchivo),
                            'estado'  => 'pendiente',
                        ]);
                    } else {
                        // Guardar el documento
                        Documento::create([
                            'user_id'           => $user->id,
                            'archivo'           => str_replace('public/', '', $rutaArchivo),
                            'estado'            => 'pendiente',
                            'documentable_id'   => $user->id,
                            'documentable_type' => User::class,
                        ]);
                    }
                }
    
                return $user->fresh(); // Retornar el usuario actualizado al finalizar la transacción
            });
    
            // Agregar la URL del archivo a cada documento si existe
            foreach ($user->documentosUser as $documento) {
                if (!empty($documento->archivo)) {
                    $documento->archivo_url = asset('storage/' . $documento->archivo);
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

        /**
     * Obtener el usuario autenticado.
     *
     * @OA\Get(
     *     path="/auth/obtener-usuario-autenticado",
     *     tags={"Auth"},
     *     summary="Obtener usuario autenticado",
     *     description="Devuelve la información del usuario autenticado, incluyendo los documentos asociados.",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Usuario autenticado obtenido exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="municipio_id", type="integer", example=1),
     *                 @OA\Property(property="tipo_identificacion", type="string", example="Cédula de ciudadanía"),
     *                 @OA\Property(property="numero_identificacion", type="string", example="123456789"),
     *                 @OA\Property(property="genero", type="string", example="Masculino"),
     *                 @OA\Property(property="primer_nombre", type="string", example="Juan"),
     *                 @OA\Property(property="segundo_nombre", type="string", example="Carlos"),
     *                 @OA\Property(property="primer_apellido", type="string", example="Pérez"),
     *                 @OA\Property(property="segundo_apellido", type="string", example="Gómez"),
     *                 @OA\Property(property="fecha_nacimiento", type="string", format="date", example="1990-01-01"),
     *                 @OA\Property(property="estado_civil", type="string", example="Soltero"),
     *                 @OA\Property(property="email", type="string", example="juan.perez@example.com"),
     *
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Usuario no autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario no autenticado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al obtener el usuario autenticado"),
     *             @OA\Property(property="error", type="string", example="Detalles del error...")
     *         )
     *     )
     * )
     */


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




    /**
     * Actualizar la contraseña del usuario autenticado.
     *
     * @OA\Post(
     *     path="/auth/actualizar-contrasena/{id}",
     *     tags={"Auth"},
     *     summary="Actualizar contraseña",
     *     description="Permite al usuario autenticado actualizar su contraseña actual.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password", "new_password"},
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Contraseña actual"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newpassword123", description="Nueva contraseña")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Contraseña actualizada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Contraseña actual incorrecta",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Contraseña incorrecta")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error en los datos proporcionados")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al actualizar la contraseña"),
     *             @OA\Property(property="error", type="string", example="Detalles del error...")
     *         )
     *     )
     * )
     */
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
            ],$e->getCode() ?: 500);
        }
    }



    /**
     * Restablecer la contraseña de un usuario.
     *
     * @OA\Post(
     *     path="/auth/restablecer-contrasena",
     *     tags={"Auth"},
     *     summary="Restablecer contraseña",
     *     description="Envía un correo electrónico con un enlace para restablecer la contraseña del usuario.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="juan.perez@example.com", description="Correo electrónico del usuario")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Correo electrónico enviado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Correo electrónico enviado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuario no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error en los datos proporcionados")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error al enviar el correo electrónico"),
     *             @OA\Property(property="error", type="string", example="Detalles del error...")
     *         )
     *     )
     * )
     */
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
