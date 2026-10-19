<script setup>
import { computed, ref } from 'vue';

const props = defineProps({ initial: { type: Array, default: () => [] } });

const expenses = ref(props.initial);
const form = ref({ spent_on: new Date().toISOString().slice(0, 10), amount: '', description: '' });
const receipt = ref(null);
const fileInput = ref(null);
const errors = ref({});
const saving = ref(false);

const total = computed(() => expenses.value.reduce((sum, e) => sum + Number(e.amount), 0));

async function refresh() {
    const res = await fetch('/api/expenses', { headers: { Accept: 'application/json' } });
    expenses.value = await res.json();
}

async function save() {
    saving.value = true;
    errors.value = {};

    const body = new FormData();
    Object.entries(form.value).forEach(([k, v]) => body.append(k, v));
    if (receipt.value) body.append('receipt', receipt.value);

    const res = await fetch('/api/expenses', { method: 'POST', body, headers: { Accept: 'application/json' } });
    saving.value = false;

    if (res.status === 422) {
        errors.value = (await res.json()).errors;
        return;
    }

    form.value.amount = '';
    form.value.description = '';
    receipt.value = null;
    fileInput.value.value = '';
    await refresh();
}

async function remove(expense) {
    if (!confirm(`Delete "${expense.description}"?`)) return;
    await fetch(`/api/expenses/${expense.id}`, { method: 'DELETE', headers: { Accept: 'application/json' } });
    await refresh();
}

const money = (n) => Number(n).toFixed(2);
</script>

<template>
    <main class="mx-auto max-w-3xl p-4 sm:p-8">
        <h1 class="mb-6 text-2xl font-semibold">DevOps Project - Expense Tracker</h1>

        <form class="mb-8 grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2" @submit.prevent="save">
            <label class="grid gap-1 text-sm">
                Date
                <input v-model="form.spent_on" type="date" class="rounded border px-2 py-1" />
                <span v-if="errors.spent_on" class="text-red-600">{{ errors.spent_on[0] }}</span>
            </label>
            <label class="grid gap-1 text-sm">
                Amount
                <input v-model="form.amount" type="number" step="0.01" min="0" class="rounded border px-2 py-1" />
                <span v-if="errors.amount" class="text-red-600">{{ errors.amount[0] }}</span>
            </label>
            <label class="grid gap-1 text-sm sm:col-span-2">
                Description
                <input v-model="form.description" type="text" class="rounded border px-2 py-1" />
                <span v-if="errors.description" class="text-red-600">{{ errors.description[0] }}</span>
            </label>
            <label class="grid gap-1 text-sm sm:col-span-2">
                Receipt (jpg, png, pdf — optional)
                <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.pdf" @change="receipt = $event.target.files[0]" />
                <span v-if="errors.receipt" class="text-red-600">{{ errors.receipt[0] }}</span>
            </label>
            <button :disabled="saving" class="rounded bg-slate-900 px-4 py-2 text-white disabled:opacity-50 sm:col-span-2">
                {{ saving ? 'Saving…' : 'Add expense' }}
            </button>
        </form>

        <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="border-b text-left text-slate-500">
                    <tr>
                        <th class="p-3">Date</th>
                        <th class="p-3">Description</th>
                        <th class="p-3 text-right">Amount</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="e in expenses" :key="e.id" class="border-b last:border-0">
                        <td class="p-3 whitespace-nowrap">{{ e.spent_on }}</td>
                        <td class="p-3">{{ e.description }}</td>
                        <td class="p-3 text-right tabular-nums">{{ money(e.amount) }}</td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <a v-if="e.has_receipt" :href="`/api/expenses/${e.id}/receipt`" class="mr-3 text-blue-600">Receipt</a>
                            <button class="text-red-600" @click="remove(e)">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!expenses.length">
                        <td colspan="4" class="p-6 text-center text-slate-500">No expenses yet.</td>
                    </tr>
                </tbody>
                <tfoot v-if="expenses.length" class="font-semibold">
                    <tr>
                        <td class="p-3" colspan="2">Total</td>
                        <td class="p-3 text-right tabular-nums">{{ money(total) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </main>
</template>
