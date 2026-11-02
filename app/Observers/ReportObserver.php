<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\Reportes;
use App\Notifications\NewReportCreatedNotification;
use App\Notifications\ReportResolvedNotification;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Notification;

class ReportObserver
{
    /**
     * Se ejecuta al crear el reporte.
     */
    public function created(Reportes $reportes): void
    {
        // 1. Enviar CORREO a quien reportó (Usuario registrado o columna 'correo')
        if ($reportes->user && filled($reportes->user->email)) {
            $reportes->user->notify(new NewReportCreatedNotification($reportes));
        } elseif (filled($reportes->correo)) {
            Notification::route('mail', $reportes->correo)
                ->notify(new NewReportCreatedNotification($reportes));
        }

        // 2. Enviar NOTIFICACIÓN INTERNA (Campanita) a los admins con permiso o Super Admin
        $admins = Admin::permission('update:reportes')
            ->orWhereHas('roles', fn($q) => $q->whereIn('name', ['super_admin', 'Super Admin']))
            ->get();

        if ($admins->isNotEmpty()) {
            $nombreUsuario = $reportes->user?->name ?? $reportes->correo ?? 'Anónimo';
            $tituloRecurso = $reportes->recurso?->metadata['titulo'] ?? "Recurso #{$reportes->recursos_id}";

            $notification = FilamentNotification::make()
                ->title("Nuevo reporte recibido (#{$reportes->id})")
                ->body("De: {$nombreUsuario} — Recurso: {$tituloRecurso}")
                ->icon('heroicon-o-exclamation-triangle')
                ->warning()
                ->actions([
                    Action::make('view')
                        ->label('Ver reportes')
                        // Como desactivaste EditAction y resuelves en la tabla, apunta al listado:
                        ->url("/admin/reportes")
                        ->markAsRead(),
                ])
                ->toDatabase();

            foreach ($admins as $admin) {
                $admin->notifyNow($notification);
            }
        }
    }

    /**
     * Se ejecuta cuando el admin da clic en "Marcar como resuelto" en ReportesTable.
     */
    public function updated(Reportes $reportes): void
    {
        // Verificamos que el campo 'resuelto' haya pasado de vacío a tener contenido
        if ($reportes->wasChanged('resuelto') && filled($reportes->resuelto)) {
            if ($reportes->user && filled($reportes->user->email)) {
                $reportes->user->notify(new ReportResolvedNotification($reportes));
            } elseif (filled($reportes->correo)) {
                Notification::route('mail', $reportes->correo)
                    ->notify(new ReportResolvedNotification($reportes));
            }
        }
    }
}