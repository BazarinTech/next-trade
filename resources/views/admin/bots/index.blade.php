@extends('layouts.app')
@section('title', 'Bot Management — Admin')
@section('page-title', 'Bot Management')
@section('page-subtitle', 'Manage bot investment plans and monitor performance')

@section('content')

<div x-data="adminBotManager()" x-init="init()">

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ session('error') }}</div>
@endif

{{-- Stats Widgets --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @php
        $widgets = [
            ['label'=>'Active Investments', 'value'=>$totalActiveInvestments,                  'color'=>'cyan',    'sub'=>'currently running'],
            ['label'=>'Principal Invested',  'value'=>'$'.number_format($totalPrincipal,2),     'color'=>'emerald', 'sub'=>'active bots total'],
            ['label'=>'Total Earnings',      'value'=>'$'.number_format($totalEarningsCredited,2),'color'=>'purple', 'sub'=>'all time credited'],
            ['label'=>'Today\'s Earnings',   'value'=>'$'.number_format($todayEarnings,2),      'color'=>'amber',   'sub'=>'credited today'],
        ];
    @endphp
    @foreach($widgets as $w)
    <div class="rounded-2xl border p-5" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
        <p class="text-xs text-gray-500 mb-1">{{ $w['label'] }}</p>
        <p class="text-2xl font-bold text-{{ $w['color'] }}-400">{{ $w['value'] }}</p>
        <p class="text-xs mt-1 text-gray-600">{{ $w['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- Most Popular + Add Button --}}
<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        @if($mostPopularPlan)
        <div class="flex items-center gap-2 text-xs px-3 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/></svg>
            Most popular: <strong>{{ $mostPopularPlan->name }}</strong> ({{ $mostPopularPlan->active_investments_count }} active)
        </div>
        @endif
    </div>
    <button @click="openCreate()"
            class="flex items-center gap-2 text-xs px-4 py-2.5 rounded-xl bg-cyan-500 text-white hover:bg-cyan-600 transition-colors font-semibold shadow-lg shadow-cyan-500/20">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Bot Plan
    </button>
</div>

{{-- Plans Table --}}
<div class="rounded-2xl border overflow-hidden mb-8" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b text-xs font-semibold text-gray-500" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
                    <th class="px-5 py-3 text-left">Plan</th>
                    <th class="px-5 py-3 text-center">Daily ROI</th>
                    <th class="px-5 py-3 text-center">Min / Max</th>
                    <th class="px-5 py-3 text-center">Duration</th>
                    <th class="px-5 py-3 text-center">Risk</th>
                    <th class="px-5 py-3 text-center">Active Users</th>
                    <th class="px-5 py-3 text-right">Total Invested</th>
                    <th class="px-5 py-3 text-right">Total Earned</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                @php $rc = $plan->risk_color; @endphp
                <tr class="border-b last:border-0 transition-colors" :class="isDark ? 'border-gray-800/40 hover:bg-gray-800/20' : 'border-gray-50 hover:bg-gray-50'">
                    <td class="px-5 py-4">
                        <p class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">{{ $plan->name }}</p>
                        <p class="text-[10px] text-gray-500 font-mono">{{ $plan->slug }}</p>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-sm font-bold text-{{ $rc }}-400">{{ $plan->daily_roi_percent }}%</span>
                    </td>
                    <td class="px-5 py-4 text-center text-xs text-gray-400">
                        ${{ number_format($plan->min_investment,2) }} / {{ $plan->max_investment ? '$'.number_format($plan->max_investment,2) : '∞' }}
                    </td>
                    <td class="px-5 py-4 text-center text-xs text-gray-400">{{ $plan->duration_label }}</td>
                    <td class="px-5 py-4 text-center">
                        <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $rc }}-500/10 text-{{ $rc }}-400 border border-{{ $rc }}-500/20 capitalize">{{ $plan->risk_level }}</span>
                    </td>
                    <td class="px-5 py-4 text-center text-xs text-gray-400">{{ $plan->active_investments_count ?? 0 }}</td>
                    <td class="px-5 py-4 text-right text-xs font-mono text-gray-400">${{ number_format($plan->total_principal ?? 0,2) }}</td>
                    <td class="px-5 py-4 text-right text-xs font-bold text-emerald-400">${{ number_format($plan->total_earned_all ?? 0,2) }}</td>
                    <td class="px-5 py-4 text-center">
                        <form method="POST" action="{{ route('admin.bots.toggle', $plan) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs px-2 py-0.5 rounded-full border transition-colors
                                {{ $plan->status === 'active'
                                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20'
                                    : 'bg-red-500/10 text-red-400 border-red-500/20 hover:bg-red-500/20' }}">
                                {{ ucfirst($plan->status) }}
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button @click="openEdit({{ json_encode([
                                'id'               => $plan->id,
                                'name'             => $plan->name,
                                'slug'             => $plan->slug,
                                'description'      => $plan->description,
                                'daily_roi_percent'=> (float)$plan->daily_roi_percent,
                                'min_investment'   => (float)$plan->min_investment,
                                'max_investment'   => $plan->max_investment ? (float)$plan->max_investment : null,
                                'duration_days'    => $plan->duration_days,
                                'risk_level'       => $plan->risk_level,
                                'status'           => $plan->status,
                                'sort_order'       => (int)$plan->sort_order,
                            ]) }})"
                                    class="text-xs text-cyan-400 hover:text-cyan-300 px-2 py-1 rounded hover:bg-cyan-500/10 transition-colors">Edit</button>
                            <form method="POST" action="{{ route('admin.bots.destroy', $plan) }}"
                                  onsubmit="return confirm('Delete {{ $plan->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-300 px-2 py-1 rounded hover:bg-red-500/10 transition-colors">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="px-5 py-10 text-center text-sm text-gray-500">No bot plans found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent Investments --}}
<div class="rounded-2xl border" :class="isDark ? 'bg-gray-900/60 border-gray-800/60' : 'bg-white border-gray-200 shadow-sm'">
    <div class="p-5 border-b" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
        <h3 class="text-sm font-semibold" :class="isDark ? 'text-white' : 'text-gray-900'">Recent Investments</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b text-xs font-semibold text-gray-500" :class="isDark ? 'border-gray-800/60' : 'border-gray-100'">
                    <th class="px-5 py-3 text-left">User</th>
                    <th class="px-5 py-3 text-left">Plan</th>
                    <th class="px-5 py-3 text-right">Principal</th>
                    <th class="px-5 py-3 text-right">Earned</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-left">Started</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentInvestments as $inv)
                @php $sc = $inv->status_color; @endphp
                <tr class="border-b last:border-0 transition-colors" :class="isDark ? 'border-gray-800/40 hover:bg-gray-800/20' : 'border-gray-50 hover:bg-gray-50'">
                    <td class="px-5 py-3">
                        <p class="text-xs font-medium" :class="isDark ? 'text-gray-300' : 'text-gray-700'">{{ $inv->user->name }}</p>
                        <p class="text-[10px] text-gray-500">{{ $inv->user->email }}</p>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $inv->botPlan->name }}</td>
                    <td class="px-5 py-3 text-right text-xs font-mono text-cyan-400">${{ number_format($inv->principal_amount,2) }}</td>
                    <td class="px-5 py-3 text-right text-xs font-bold text-emerald-400">${{ number_format($inv->total_earned,2) }}</td>
                    <td class="px-5 py-3 text-center">
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-{{ $sc }}-500/10 text-{{ $sc }}-400 border border-{{ $sc }}-500/20">{{ ucfirst($inv->status) }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $inv->started_at->format('M d, Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">No investments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Create / Edit Modal --}}
<div x-show="showModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showModal = false">
    <div class="w-full max-w-lg rounded-2xl border shadow-2xl overflow-y-auto max-h-[90vh]"
         :class="isDark ? 'bg-gray-900 border-gray-800' : 'bg-white border-gray-200'" @click.stop x-transition.scale>

        <div class="flex items-center justify-between p-6 border-b sticky top-0 z-10" :class="isDark ? 'border-gray-800 bg-gray-900' : 'border-gray-100 bg-white'">
            <h2 class="text-sm font-bold" :class="isDark ? 'text-white' : 'text-gray-900'" x-text="editId ? 'Edit Bot Plan' : 'Add Bot Plan'"></h2>
            <button @click="showModal = false" class="p-1 rounded-lg text-gray-500 hover:text-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form :action="editId ? `/admin/bots/${editId}` : '{{ route('admin.bots.store') }}'"
              method="POST" class="p-6 space-y-4">
            @csrf
            <input x-show="editId" type="hidden" name="_method" value="PUT">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" x-model="form.name" required maxlength="100"
                           @input="autoSlug()"
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Slug <span class="text-red-400">*</span></label>
                    <input type="text" name="slug" x-model="form.slug" required maxlength="100"
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Description</label>
                <textarea name="description" x-model="form.description" rows="2" maxlength="500"
                          class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50 resize-none"
                          :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Daily ROI % <span class="text-red-400">*</span></label>
                    <input type="number" name="daily_roi_percent" x-model="form.daily_roi_percent" min="0" max="100" step="0.01" required
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Duration (days)</label>
                    <input type="number" name="duration_days" x-model="form.duration_days" min="1" step="1" placeholder="Unlimited"
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Min Investment <span class="text-red-400">*</span></label>
                    <input type="number" name="min_investment" x-model="form.min_investment" min="0.01" step="0.01" required
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Max Investment</label>
                    <input type="number" name="max_investment" x-model="form.max_investment" min="0.01" step="0.01" placeholder="Unlimited"
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Risk Level <span class="text-red-400">*</span></label>
                    <select name="risk_level" x-model="form.risk_level" required
                            class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                            :class="isDark ? 'border-gray-700 text-white bg-gray-900' : 'border-gray-300 text-gray-900 bg-white'">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="extreme">Extreme</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Status <span class="text-red-400">*</span></label>
                    <select name="status" x-model="form.status" required
                            class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                            :class="isDark ? 'border-gray-700 text-white bg-gray-900' : 'border-gray-300 text-gray-900 bg-white'">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Sort Order</label>
                    <input type="number" name="sort_order" x-model="form.sort_order" min="0" step="1"
                           class="w-full text-sm px-3 py-2.5 rounded-xl border bg-transparent focus:outline-none focus:ring-1 focus:ring-cyan-500/50"
                           :class="isDark ? 'border-gray-700 text-white' : 'border-gray-300 text-gray-900'">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-cyan-500 text-white text-sm font-semibold hover:bg-cyan-600 transition-colors">
                    <span x-text="editId ? 'Update Plan' : 'Create Plan'"></span>
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
function adminBotManager() {
    return {
        showModal: false,
        editId:    null,
        form: {
            name: '', slug: '', description: '', daily_roi_percent: '',
            min_investment: '', max_investment: '', duration_days: '',
            risk_level: 'medium', status: 'active', sort_order: 0,
        },

        init() {},

        openCreate() {
            this.editId = null;
            this.form = { name:'', slug:'', description:'', daily_roi_percent:'', min_investment:'', max_investment:'', duration_days:'', risk_level:'medium', status:'active', sort_order:0 };
            this.showModal = true;
        },

        openEdit(plan) {
            this.editId = plan.id;
            this.form = { ...plan };
            this.showModal = true;
        },

        autoSlug() {
            if (!this.editId) {
                this.form.slug = this.form.name
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-');
            }
        },
    };
}
</script>
@endpush

@endsection
