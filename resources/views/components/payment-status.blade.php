@props(['status' => 'pending'])
@php($labels = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'completed' => 'Selesai'])
<span {{ $attributes->class(['admin-status', 'status-' . (array_key_exists($status, $labels) ? $status : 'pending')]) }}><span></span>{{ $labels[$status] ?? $status }}</span>
