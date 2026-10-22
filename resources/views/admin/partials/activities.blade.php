<ul class="admin-activity-list">
    @forelse($activities as $activity)
        <li><span class="admin-activity-icon"><x-icon :name="$activity['type']" class="size-4" /></span><div class="min-w-0 flex-1"><p class="text-xs font-medium">{{ $activity['title'] }}</p><p class="mt-1 text-xs leading-5 text-muted">{{ $activity['description'] }}</p></div><time datetime="{{ $activity['created_at']->toIso8601String() }}" class="shrink-0 text-[10px] text-muted">{{ $activity['created_at']->locale('id')->diffForHumans() }}</time></li>
    @empty
        <li class="justify-center py-12 text-sm text-muted">Belum ada aktivitas untuk ditampilkan.</li>
    @endforelse
</ul>
