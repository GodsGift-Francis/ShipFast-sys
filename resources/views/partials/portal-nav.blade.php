<nav class="flex-1 space-y-1 overflow-y-auto p-4">
    <p class="px-3 pb-2 font-mono text-[10px] font-bold uppercase tracking-wider text-primary-foreground/40">Operations</p>
    @foreach ($nav as [$route, $label, $path])
        @php($active = request()->routeIs($route) || request()->routeIs(str_replace('.index','.*',$route)))
        <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm transition-colors {{ $active ? 'bg-accent/15 font-semibold text-accent' : 'text-primary-foreground/70 hover:bg-primary-foreground/10' }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
            {{ $label }}
        </a>
    @endforeach
    @if (auth()->user()?->isSuperAdmin())
        <p class="px-3 pb-2 pt-4 font-mono text-[10px] font-bold uppercase tracking-wider text-primary-foreground/40">Control</p>
        <a href="{{ route('admin.index') }}" class="flex items-center gap-3 rounded-md px-3 py-2.5 text-sm transition-colors {{ request()->routeIs('admin.*') ? 'bg-accent/15 font-semibold text-accent' : 'text-primary-foreground/70 hover:bg-primary-foreground/10' }}">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.6-2.6A9 9 0 1112 3a9 9 0 018.6 5.4z"/></svg>
            Super Admin
        </a>
    @endif
</nav>
