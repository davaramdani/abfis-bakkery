@if(session('success'))
    <div role="status" class="notice mb-6"><x-icon name="check" class="size-4" /><p>{{ session('success') }}</p><button type="button" class="ml-auto p-1" data-dismiss-notice aria-label="Tutup notifikasi"><x-icon name="x" class="size-4" /></button></div>
@endif
@if(session('error'))
    <div role="alert" class="notice mb-6 border-[#d9b7a7] bg-[#f1e3da] text-[#874a36]">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div role="alert" class="mb-6 border border-[#d9b7a7] bg-[#f1e3da] p-5 text-sm text-[#874a36]">
        <p class="mb-2 font-medium">Periksa kembali data berikut.</p>
        <ul class="list-inside list-disc space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
