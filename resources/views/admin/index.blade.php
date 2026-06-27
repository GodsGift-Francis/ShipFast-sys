@extends('layouts.portal')
@section('title', 'Super Admin — ShipFast')
@section('heading', 'Super Admin')
@section('content')
@php($roleBadge = fn($r) => match($r){'super_admin'=>'bg-accent/15 text-accent','admin'=>'bg-blue-100 text-blue-800',default=>'bg-gray-100 text-gray-700'})
<div class="mb-4 rounded-xl border border-accent/30 bg-accent/5 p-4">
    <p class="font-mono text-[11px] font-bold uppercase tracking-wider text-accent">Restricted area</p>
    <p class="mt-1 text-sm text-muted-foreground">Manage user roles, account status, and credentials. You cannot modify or delete your own account, suspend a super admin, or remove the last super admin.</p>
</div>

<form method="GET" class="mb-4 flex gap-2">
    <input name="q" value="{{ request('q') }}" placeholder="Search users" class="min-w-0 flex-1 rounded-lg border border-input bg-background px-3 py-2 text-sm">
    <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground">Search</button>
</form>

<div class="space-y-3">
    @foreach ($users as $u)
        <div class="rounded-xl border border-border bg-card p-4" x-data="{ pw:false }">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-full bg-secondary text-sm font-bold">{{ strtoupper(substr($u->name ?: $u->email, 0, 1)) }}</span>
                    <div>
                        <div class="font-semibold">{{ $u->name ?: '—' }} @if (auth()->id() === $u->id)<span class="text-xs text-muted-foreground">(you)</span>@endif</div>
                        <div class="text-xs text-muted-foreground">{{ $u->email }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $roleBadge($u->role) }}">{{ \App\Support\Status::label($u->role) }}</span>
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $u->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">{{ ucfirst($u->status) }}</span>
                </div>
            </div>

            @if (auth()->id() !== $u->id)
                <div class="mt-4 flex flex-wrap items-end gap-3 border-t border-border pt-4">
                    <form method="POST" action="{{ route('admin.users.update', $u) }}" class="flex flex-wrap items-end gap-2">
                        @csrf @method('PATCH')
                        <div><label class="text-[11px] font-medium text-muted-foreground">Role</label>
                            <select name="role" class="mt-1 block rounded-lg border border-input bg-background px-3 py-2 text-sm">
                                @foreach (['customer','admin','super_admin'] as $r)<option value="{{ $r }}" @selected($u->role===$r)>{{ \App\Support\Status::label($r) }}</option>@endforeach
                            </select>
                        </div>
                        <div><label class="text-[11px] font-medium text-muted-foreground">Status</label>
                            <select name="status" class="mt-1 block rounded-lg border border-input bg-background px-3 py-2 text-sm">
                                @foreach (['active','suspended'] as $s)<option value="{{ $s }}" @selected($u->status===$s)>{{ ucfirst($s) }}</option>@endforeach
                            </select>
                        </div>
                        <button class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground">Save</button>
                    </form>

                    <button @click="pw=!pw" class="rounded-lg border border-border px-4 py-2 text-sm font-semibold">Reset password</button>

                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Delete {{ $u->email }}? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button class="rounded-lg border border-destructive/40 px-4 py-2 text-sm font-semibold text-destructive">Delete</button>
                    </form>

                    <form method="POST" action="{{ route('admin.users.password', $u) }}" x-show="pw" x-cloak class="flex items-end gap-2" style="display:none">
                        @csrf @method('PATCH')
                        <input name="password" type="text" placeholder="New password (min 8)" class="rounded-lg border border-input bg-background px-3 py-2 text-sm" required>
                        <button class="rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground">Set</button>
                    </form>
                </div>
            @endif
        </div>
    @endforeach
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
