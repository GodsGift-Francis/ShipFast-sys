<div class="space-y-3 border-t border-primary-foreground/10 p-4">
    <div class="flex items-center gap-3 px-2">
        <span class="grid h-8 w-8 place-items-center rounded-full bg-primary-foreground/10 text-xs font-bold">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
        <div class="min-w-0">
            <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-primary-foreground/60">{{ auth()->user()->email }}</p>
        </div>
    </div>
    <form method="POST" action="{{ route('logout') }}">@csrf
        <button class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm text-primary-foreground/70 hover:bg-primary-foreground/10">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
            Log out
        </button>
    </form>
</div>
