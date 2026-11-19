<x-layouts::auth :title="__('Register')" class="max-w-2xl">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Sección 1: Datos Personales -->
            <div class="flex flex-col gap-4">
                <h3
                    class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    Datos personales
                </h3>

                <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                    <flux:input name="nombres" :label="__('Nombre(s)')" :value="old('nombres')" type="text" required
                        autofocus autocomplete="given-name" placeholder="Ej. Juan Carlos" />

                    <flux:input name="apellidos" :label="__('Apellido(s)')" :value="old('apellidos')" type="text"
                        required autocomplete="family-name" placeholder="Ej. Pérez López" />
                </div>

                <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                    <flux:input name="nacionalidad" :label="__('Nacionalidad')" :value="old('nacionalidad')"
                        type="text" required placeholder="Ej. Mexicana" />

                    <flux:input name="fecha_nacimiento" :label="__('Fecha de nacimiento')"
                        :value="old('fecha_nacimiento')" type="date" required />
                </div>

                <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                    <flux:select name="sexo" :label="__('Sexo')" required>
                        <option value="" disabled {{ old('sexo') ? '' : 'selected' }}>Selecciona una opción
                        </option>
                        <option value="hombre" {{ old('sexo') == 'hombre' ? 'selected' : '' }}>Hombre</option>
                        <option value="mujer" {{ old('sexo') == 'mujer' ? 'selected' : '' }}>Mujer</option>
                        <option value="prefiero_no_decirlo"
                            {{ old('sexo') == 'prefiero_no_decirlo' ? 'selected' : '' }}>Prefiero no decirlo</option>
                    </flux:select>

                    <flux:input name="ciudad_pais_residencia" :label="__('Ciudad y país de residencia')"
                        :value="old('ciudad_pais_residencia')" type="text" required
                        placeholder="Ej. Guadalajara, México" />
                </div>
            </div>

            <!-- Sección 2: Perfil Académico y Consulta -->
            <div class="flex flex-col gap-4">
                <h3
                    class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    Datos académico 
                </h3>

                <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                    <flux:select name="grado_estudios" :label="__('Grado de estudios')" required>
                        <option value="" disabled {{ old('grado_estudios') ? '' : 'selected' }}>Selecciona una
                            opción</option>
                        <option value="sin_estudios" {{ old('grado_estudios') == 'sin_estudios' ? 'selected' : '' }}>Sin
                            estudios</option>
                        <option value="educacion_basica"
                            {{ old('grado_estudios') == 'educacion_basica' ? 'selected' : '' }}>Educación básica
                        </option>
                        <option value="bachillerato" {{ old('grado_estudios') == 'bachillerato' ? 'selected' : '' }}>
                            Bachillerato</option>
                        <option value="licenciatura" {{ old('grado_estudios') == 'licenciatura' ? 'selected' : '' }}>
                            Licenciatura</option>
                        <option value="posgrado" {{ old('grado_estudios') == 'posgrado' ? 'selected' : '' }}>Posgrado
                        </option>
                    </flux:select>

                    <flux:select name="motivo_consulta" :label="__('Motivo de consulta')" required>
                        <option value="" disabled {{ old('motivo_consulta') ? '' : 'selected' }}>Selecciona una
                            opción</option>
                        <option value="personal" {{ old('motivo_consulta') == 'personal' ? 'selected' : '' }}>Personal
                        </option>
                        <option value="escolar_tesis"
                            {{ old('motivo_consulta') == 'escolar_tesis' ? 'selected' : '' }}>Escolar o tesis</option>
                        <option value="investigacion_academica_profesional"
                            {{ old('motivo_consulta') == 'investigacion_academica_profesional' ? 'selected' : '' }}>
                            Investigación académica o profesional</option>
                    </flux:select>
                </div>

                <flux:input name="institucion_adscripcion" :label="__('Institución de adscripción (opcional)')"
                    :value="old('institucion_adscripcion')" type="text"
                    placeholder="Ej. Universidad de Guadalajara" />

                <flux:textarea name="tema_investigacion" :label="__('Tema de investigación (opcional)')" rows="2"
                    placeholder="Describe brevemente el tema o proyecto de tu consulta...">
                    {{ old('tema_investigacion') }}</flux:textarea>
            </div>

            <!-- Sección 3: Datos de Acceso -->
            <div class="flex flex-col gap-4">
                <h3
                    class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 border-b border-zinc-200 dark:border-zinc-800 pb-2">
                    Datos de acceso
                </h3>

                <flux:input name="email" :label="__('Correo electrónico')" :value="old('email')" type="email"
                    required autocomplete="email" placeholder="correo@ejemplo.com" />

                <div class="grid grid-cols-1 gap-4 @md:grid-cols-2">
                    <flux:input name="password" :label="__('Password')" type="password" required
                        autocomplete="new-password" :placeholder="__('Password')" viewable />

                    <flux:input name="password_confirmation" :label="__('Confirm password')" type="password" required
                        autocomplete="new-password" :placeholder="__('Confirm password')" viewable />
                </div>
            </div>

            <div class="flex items-center justify-end pt-2">
                <flux:button type="submit" variant="primary" class="w-full dark:text-white" data-test="register-user-button"
                    style="background:#7c2422;">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
