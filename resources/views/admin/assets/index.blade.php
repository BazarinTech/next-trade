@extends('layouts.app')
@section('title', 'Asset Management | Admin')
@section('page-title', 'Asset Management')
@section('page-subtitle', 'Manage tradeable instruments and market parameters')

@section('content')

<div x-data="assetManager()" x-init="init()">

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
    {{ session('error') }}
</div>
@endif

<!-- Header Row -->
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <span class="text-xs px-2.5 py-1 rounded-full bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 font-medium">
            {{ $assets->count() }} assets
        </span>
        <span class="text-xs px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">
            {{ $assets->where('is_active', true)->count() }} active
        </span>
    </div>
    <button @click="openCreate()" class="flex items-center gap-2 text-xs px-4 py-2.5 rounded-xl bg-cyan-500 text-white hover:bg-cyan-600 transition-colors font-semibold shadow-lg shadow-cyan-500/20">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Asset
    </button>
</div>

<!-- Assets Table -->
<div class="rounded-2xl border overflow-hidden" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b text-xs font-semibold text-gray-500" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
                    <th class="px-5 py-3 text-left">Symbol / Name</th>
                    <th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-right">Base Price</th>
                    <th class="px-5 py-3 text-right">Current Price</th>
                    <th class="px-5 py-3 text-center">Volatility</th>
                    <th class="px-5 py-3 text-center">Trend</th>
                    <th class="px-5 py-3 text-center">Order</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                @php
                    $typeColors = ['crypto'=>'amber','forex'=>'cyan','synthetic'=>'purple','stock'=>'emerald'];
                    $trendColors = ['bullish'=>'emerald','bearish'=>'red','neutral'=>'gray'];
                    $tc = $typeColors[$asset->type] ?? 'gray';
                    $rc = $trendColors[$asset->trend_bias] ?? 'gray';
                @endphp
                <tr class="border-b transition-colors" :class="isDark ? 'border-gray-800/40 hover:bg-gray-800/20' : 'border-gray-50 hover:bg-gray-50'">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-{{ $tc }}-500/15 border border-{{ $tc }}-500/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-[10px] font-bold text-{{ $tc }}-400">{{ strtoupper(substr($asset->symbol, 0, 2)) }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $asset->symbol }}</p>
                                <p class="text-xs text-gray-500">{{ $asset->name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $tc }}-500/10 text-{{ $tc }}-400 border border-{{ $tc }}-500/20 capitalize">{{ $asset->type }}</span>
                    </td>
                    <td class="px-5 py-4 text-right text-xs font-mono" :class="isDark ? 'text-gray-300' : 'text-gray-700'">
                        {{ $asset->formatPrice($asset->base_price) }}
                    </td>
                    <td class="px-5 py-4 text-right text-xs font-mono" :class="isDark ? 'text-gray-300' : 'text-gray-700'">
                        {{ $asset->formatPrice($asset->current_price) }}
                    </td>
                    <td class="px-5 py-4 text-center text-xs text-gray-400 font-mono">
                        {{ number_format((float)$asset->volatility, 6) }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $rc }}-500/10 text-{{ $rc }}-400 border border-{{ $rc }}-500/20 capitalize">{{ $asset->trend_bias }}</span>
                    </td>
                    <td class="px-5 py-4 text-center text-xs text-gray-400">{{ $asset->sort_order }}</td>
                    <td class="px-5 py-4 text-center">
                        <form method="POST" action="{{ route('admin.assets.toggle', $asset) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs px-2 py-0.5 rounded-full border transition-colors
                                {{ $asset->is_active
                                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20'
                                    : 'bg-red-500/10 text-red-400 border-red-500/20 hover:bg-red-500/20' }}">
                                {{ $asset->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="openEdit({{ json_encode([
                                'id'            => $asset->id,
                                'symbol'        => $asset->symbol,
                                'name'          => $asset->name,
                                'type'          => $asset->type,
                                'base_price'    => (float)$asset->base_price,
                                'current_price' => (float)$asset->current_price,
                                'volatility'    => (float)$asset->volatility,
                                'trend_bias'    => $asset->trend_bias,
                                'is_active'     => (bool)$asset->is_active,
                                'sort_order'    => (int)$asset->sort_order,
                            ]) }})"
                                    class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors px-2 py-1 rounded hover:bg-cyan-500/10">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('admin.assets.destroy', $asset) }}"
                                  onsubmit="return confirm('Delete {{ $asset->symbol }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition-colors px-2 py-1 rounded hover:bg-red-500/10">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-5 py-12 text-center">
                        <p class="text-sm text-gray-500 mb-3">No assets found.</p>
                        <button @click="openCreate()" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">Add your first asset</button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create / Edit Modal -->
<div x-show="showModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showModal = false">
    <div class="w-full max-w-lg rounded-2xl border shadow-2xl" :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'"
         @click.stop x-transition.scale>

        <div class="flex items-center justify-between p-6 border-b" :class="isDark ? 'border-gray-800' : 'border-gray-100'">
            <h2 class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-gray-900'" x-text="editId ? 'Edit Asset' : 'Add New Asset'"></h2>
            <button @click="showModal = false" class="p-1 rounded-lg text-gray-500 hover:text-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form :method="editId ? 'POST' : 'POST'"
              :action="editId ? `/admin/assets/${editId}` : '{{ route('admin.assets.store') }}'"
              class="p-6 space-y-4">
            @csrf
            <input x-show="editId" type="hidden" name="_method" value="PUT">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Symbol <span class="text-red-400">*</span></label>
                    <input type="text" name="symbol" x-model="form.symbol" placeholder="BTC/USD" required maxlength="20"
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" x-model="form.name" placeholder="Bitcoin / US Dollar" required maxlength="100"
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Type <span class="text-red-400">*</span></label>
                    <select name="type" x-model="form.type" required
                            class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                            :class="isDark ? 'border-gray-700 text-white bg-gray-900' : 'border-gray-300 text-gray-900 bg-white'">
                        <option value="forex">Forex</option>
                        <option value="crypto">Crypto</option>
                        <option value="synthetic">Synthetic</option>
                        <option value="stock">Stock</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Trend Bias <span class="text-red-400">*</span></label>
                    <select name="trend_bias" x-model="form.trend_bias" required
                            class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                            :class="isDark ? 'border-gray-700 text-white bg-gray-900' : 'border-gray-300 text-gray-900 bg-white'">
                        <option value="bullish">Bullish</option>
                        <option value="neutral">Neutral</option>
                        <option value="bearish">Bearish</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Base Price <span class="text-red-400">*</span></label>
                    <input type="number" name="base_price" x-model="form.base_price" placeholder="65000" required min="0.000001" step="any"
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Current Price</label>
                    <input type="number" name="current_price" x-model="form.current_price" placeholder="Same as base" min="0.000001" step="any"
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Volatility <span class="text-red-400">*</span></label>
                    <input type="number" name="volatility" x-model="form.volatility" placeholder="0.012" required min="0" step="0.0001"
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Sort Order</label>
                    <input type="number" name="sort_order" x-model="form.sort_order" placeholder="0" min="0" step="1"
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="rounded border-gray-700 text-cyan-500 focus:ring-cyan-500/50 bg-transparent">
                    <span class="text-xs text-gray-400">Active (visible to traders)</span>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-cyan-500 text-white text-sm font-semibold hover:bg-cyan-600 transition-colors">
                    <span x-text="editId ? 'Update Asset' : 'Create Asset'"></span>
                </button>
                <button type="button" @click="showModal = false" class="flex-1 py-2.5 rounded-xl border border-gray-700 text-gray-400 text-sm hover:text-gray-200 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

</div>

@push('scripts')
<script>
function assetManager() {
    return {
        showModal: false,
        editId: null,
        form: {
            symbol: '', name: '', type: 'crypto', trend_bias: 'neutral',
            base_price: '', current_price: '', volatility: '', sort_order: 0, is_active: true,
        },
        init() {
            // Open create modal if URL has ?new=1
            if (new URLSearchParams(location.search).get('new') === '1') this.openCreate();
        },
        openCreate() {
            this.editId = null;
            this.form = { symbol: '', name: '', type: 'crypto', trend_bias: 'neutral', base_price: '', current_price: '', volatility: '', sort_order: 0, is_active: true };
            this.showModal = true;
        },
        openEdit(asset) {
            this.editId = asset.id;
            this.form = { ...asset };
            this.showModal = true;
        },
    };
}
</script>
@endpush

@endsection
