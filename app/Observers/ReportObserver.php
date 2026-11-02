<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\Reportes;
use App\Notifications\NewReportCreatedNotification;
use App\Notifications\ReportResolvedNotification;
use Illuminate\Support\Facades\Notification;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;

class ReportObserver
{
    /**
     * Handle the Reportes "created" event.
     */
    public function created(Reportes $reportes): void
    {
        // 1. Enviar CORREO al usuario que reportó
        if ($reportes->user) {
            $reportes->user->notify(new NewReportCreatedNotification($reportes));
        }

        // 2. Enviar NOTIFICACIÓN INTERNA (Campanita) a los admins con permiso de editar
        $admins = Admin::permission('update:reportes')
            ->orWhereHas('roles', fn($q) => $q->where('name', 'super_admin'))
            ->get();

        \Log::info('Enviando notificación a IDs: ' . $admins->pluck('id')->implode(', '));

        if ($admins->isNotEmpty()) {
            FilamentNotification::make()
                ->title("Nuevo reporte recibido (#{$reportes->id})")
                ->body("Usuario: {$reportes->user?->name} - Motivo: " . ($reportes->observaciones ?? $reportes->motivo ?? 'Sin especificar'))
                ->icon('heroicon-o-exclamation-triangle')
                ->warning()
                ->actions([
                    Action::make('view')
                        ->label('Atender reporte')
                        ->url("/admin/reportes/{$reportes->id}/edit")
                        ->markAsRead(),
                ])
                ->sendToDatabase($admins);
        }
    }

    /**
     * Handle the Reportes "updated" event.
     */
    public function updated(Reportes $reportes): void
    {
        if ($reportes->wasChanged('status') && $reportes->status === 'resolved') {
            if ($reportes->user) {
                $reportes->user->notify(new ReportResolvedNotification($reportes));
            }
        }
    }

    /**
     * Handle the Reportes "deleted" event.
     */
    public function deleted(Reportes $reportes): void
    {
        //
    }

    /**
     * Handle the Reportes "restored" event.
     */
    public function restored(Reportes $reportes): void
    {
        //
    }

    /**
     * Handle the Reportes "force deleted" event.
     */
    public function forceDeleted(Reportes $reportes): void
    {
        //
    }
}
