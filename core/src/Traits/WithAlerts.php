<?php

namespace Marufsharia\Hyro\Core\Traits;

trait WithAlerts
{
    /**
     * Dispatch success alert
     */
    public function alertSuccess($title, $message = '')
    {
        $this->dispatch('alert:success', [
            'title' => $title,
            'message' => $message
        ]);
    }

    /**
     * Dispatch error alert
     */
    public function alertError($title, $message = '')
    {
        $this->dispatch('alert:error', [
            'title' => $title,
            'message' => $message
        ]);
    }

    /**
     * Dispatch warning alert
     */
    public function alertWarning($title, $message = '')
    {
        $this->dispatch('alert:warning', [
            'title' => $title,
            'message' => $message
        ]);
    }

    /**
     * Dispatch info alert
     */
    public function alertInfo($title, $message = '')
    {
        $this->dispatch('alert:info', [
            'title' => $title,
            'message' => $message
        ]);
    }

    /**
     * Dispatch toast notification
     */
    public function toast($message, $type = 'info', $duration = 3000)
    {
        $this->dispatch('alert:toast', [
            'message' => $message,
            'type' => $type,
            'duration' => $duration
        ]);
    }

    /**
     * Success toast
     */
    public function toastSuccess($message, $duration = 3000)
    {
        $this->toast($message, 'success', $duration);
    }

    /**
     * Error toast
     */
    public function toastError($message, $duration = 3000)
    {
        $this->toast($message, 'error', $duration);
    }

    /**
     * Warning toast
     */
    public function toastWarning($message, $duration = 3000)
    {
        $this->toast($message, 'warning', $duration);
    }

    /**
     * Info toast
     */
    public function toastInfo($message, $duration = 3000)
    {
        $this->toast($message, 'info', $duration);
    }
}

