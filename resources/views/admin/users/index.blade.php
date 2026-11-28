@extends('layouts.admin')

@section('title', 'Manajemen User - Admin')

@section('content')
<div class="space-y-6">
    <!-- Modern Header -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-gradient-to-br from-gray-600 to-slate-700 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manajemen User</h1>
                <p class="text-gray-600">Kelola pengguna sistem</p>
            </div>
        </div>
        <a href="{{ route('admin.users.create') }}" class="bg-gradient-to-r from-gray-600 to-slate-700 hover:from-gray-700 hover:to-slate-800 text-white px-6 py-3 rounded-xl font-semibold shadow-lg transition duration-200 transform hover:scale-105">
            <i class="fas fa-plus mr-1"></i> Tambah User
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg shadow">
        <div class="flex items-center">
            <svg class="w-6 h-6 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
            </svg>
            <span class="text-green-700 font-medium">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Search --}}
            <div class="space-y-1">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Cari User</label>
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama, username, email..." 
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gray-400 focus:border-transparent transition">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>

            {{-- School --}}
            <div class="space-y-1">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit Sekolah</label>
                <select name="school_id" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gray-400 focus:border-transparent transition">
                    <option value="">Semua Sekolah</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Role --}}
            <div class="space-y-1">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Role User</label>
                <select name="role" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-gray-400 focus:border-transparent transition">
                    <option value="">Semua Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                            {{ ucfirst($role) }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-gray-800 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-gray-900 transition shadow-sm">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                @if(request()->anyFilled(['q', 'school_id', 'role']))
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-red-50 text-red-600 rounded-xl text-sm font-bold hover:bg-red-100 transition border border-red-100">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="bg-gradient-to-r from-gray-600 to-slate-700 text-white">
                    <th class="p-4 text-left font-semibold">No</th>
                    <th class="p-4 text-left font-semibold">Nama</th>
                    <th class="p-4 text-left font-semibold">Username</th>
                    <th class="p-4 text-left font-semibold">Email</th>
                    <th class="p-4 text-left font-semibold">Role</th>
                    <th class="p-4 text-left font-semibold">Sekolah</th>
                    <th class="p-4 text-left font-semibold">Status</th>
                    <th class="p-4 text-center font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php $no = ($users->currentPage() - 1) * $users->perPage() + 1; @endphp
                @foreach($users as $u)
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                    <td class="p-4">
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 text-gray-600 rounded-lg font-semibold">{{ $no++ }}</span>
                    </td>
                    <td class="p-4 font-medium text-gray-800">{{ $u->name }}</td>
                    <td class="p-4 text-gray-600">{{ $u->username }}</td>
                    <td class="p-4 text-gray-600">{{ $u->email }}</td>
                    <td class="p-4">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">{{ ucwords(str_replace('_', ' ', $u->role)) }}</span>
                        @if($u->isKepalaSekolah() && $u->role !== 'kepala_sekolah')
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm font-medium ml-1">Kepsek</span>
                        @endif
                    </td>
                    <td class="p-4 text-gray-600">{{ $u->school->name ?? '-' }}</td>
                    <td class="p-4">
                        @if($u->is_active)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">Aktif</span>
                        @else
                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-medium">Nonaktif</span>
                        @endif
                    </td>
                    <td class="p-4">
                        <div class="flex items-center justify-center gap-2">
                            @if(auth()->user()->isSuperAdmin() && auth()->id() !== $u->id)
                            <form action="{{ route('admin.users.impersonate', $u) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="w-9 h-9 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg flex items-center justify-center transition transform hover:scale-110" title="Masuk Sebagai {{ $u->name }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                </button>
                            </form>
                            @endif
                            <a href="{{ route('admin.users.show', $u) }}" class="w-9 h-9 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg flex items-center justify-center transition transform hover:scale-110" title="Lihat">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('admin.users.edit', $u) }}" class="w-9 h-9 bg-green-100 hover:bg-green-200 text-green-600 rounded-lg flex items-center justify-center transition transform hover:scale-110" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <a href="{{ route('admin.users.reset-password.form', $u) }}" class="w-9 h-9 bg-yellow-100 hover:bg-yellow-200 text-yellow-600 rounded-lg flex items-center justify-center transition transform hover:scale-110" title="Reset Password">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            </a>
                            <form action="{{ route('admin.users.destroy', $u) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus user ini?')" class="w-9 h-9 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg flex items-center justify-center transition transform hover:scale-110" title="Hapus">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
</div>
@endsection