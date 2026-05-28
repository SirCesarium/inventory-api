<?php

namespace App\Observers;

use App\Models\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    private function logAction(Model $model, string $action): void
    {
        Audit::create([
            'action' => $action,
            'user_id' => Auth::user()?->id,
            'auditable_id' => $model->getKey(),
            'auditable_type' => get_class($model),
        ]);
    }

    public function created(Model $model): void
    {
        $this->logAction($model, 'create');
    }

    public function updated(Model $model): void
    {
        $this->logAction($model, 'update');
    }

    public function deleted(Model $model): void
    {
        $this->logAction($model, 'delete');
    }
}
