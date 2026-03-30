<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    members: Array,
    stats: Object,
    documentTypes: Array,
    seasons: Array,
    currentSeason: String,
});

const search = ref('');
const filterStatus = ref('all');
const showUpload = ref(false);
const uploadTarget = ref(null);

const statusColors = {
    valid: 'bg-emerald-500',
    expired: 'bg-red-500',
    missing: 'bg-gray-300',
};

const filtered = computed(() => {
    let list = props.members;
    if (search.value) {
        const q = search.value.toLowerCase();
        list = list.filter(m => m.full_name.toLowerCase().includes(q));
    }
    if (filterStatus.value === 'ok') list = list.filter(m => !m.has_issues);
    if (filterStatus.value === 'issues') list = list.filter(m => m.has_issues);
    return list;
});

const form = useForm({
    user_id: '',
    type: '',
    custom_label: '',
    file: null,
    season: props.currentSeason,
    expires_at: '',
    notes: '',
});

function openUpload(member, type = '') {
    uploadTarget.value = member;
    form.user_id = member.user_id;
    form.type = type;
    form.custom_label = '';
    form.file = null;
    form.season = props.currentSeason;
    form.expires_at = '';
    form.notes = '';
    showUpload.value = true;
}

function submitUpload() {
    form.post(route('documents.store'), {
        forceFormData: true,
        onSuccess: () => { showUpload.value = false; },
    });
}

function deleteDoc(id) {
    if (confirm('Supprimer ce document ?')) {
        router.delete(route('documents.destroy', id));
    }
}

function changeSeason(season) {
    router.get(route('documents.index'), { season }, { preserveState: true });
}
</script>

<template>
    <Head title="Documents" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-lg font-semibold text-gray-900">Documents</h2>
        </template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-100 bg-white p-4">
                    <p class="text-xs text-gray-500">Membres</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4">
                    <p class="text-xs text-gray-500">A jour</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">{{ stats.compliant }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4">
                    <p class="text-xs text-gray-500">Expirés</p>
                    <p class="mt-1 text-2xl font-bold text-red-600">{{ stats.expired }}</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4">
                    <p class="text-xs text-gray-500">Manquants</p>
                    <p class="mt-1 text-2xl font-bold text-amber-600">{{ stats.missing }}</p>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative max-w-xs flex-1">
                    <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                    <input v-model="search" type="text" placeholder="Rechercher…" class="w-full rounded-lg border border-gray-200 bg-white py-2 pl-10 pr-4 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                </div>

                <div class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white p-0.5">
                    <button class="rounded-md px-2.5 py-1.5 text-xs font-medium transition" :class="filterStatus === 'all' ? 'bg-gray-100 text-gray-900' : 'text-gray-500'" @click="filterStatus = 'all'">Tous</button>
                    <button class="rounded-md px-2.5 py-1.5 text-xs font-medium transition" :class="filterStatus === 'ok' ? 'bg-gray-100 text-gray-900' : 'text-gray-500'" @click="filterStatus = 'ok'">A jour</button>
                    <button class="rounded-md px-2.5 py-1.5 text-xs font-medium transition" :class="filterStatus === 'issues' ? 'bg-gray-100 text-gray-900' : 'text-gray-500'" @click="filterStatus = 'issues'">Problèmes</button>
                </div>

                <select class="ml-auto rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm" @change="changeSeason($event.target.value)">
                    <option v-for="s in seasons" :key="s" :value="s" :selected="s === currentSeason">{{ s }}</option>
                </select>
            </div>

            <!-- Table -->
            <div v-if="filtered.length" class="overflow-hidden rounded-xl border border-gray-100 bg-white">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-50 bg-gray-50/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-400">Membre</th>
                            <th v-for="dt in documentTypes.filter(t => t.value !== 'other')" :key="dt.value" class="hidden px-3 py-3 text-center text-xs font-medium uppercase tracking-wide text-gray-400 md:table-cell">{{ dt.label }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wide text-gray-400">Statut</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="m in filtered" :key="m.member_id" class="transition hover:bg-gray-50/50">
                            <td class="px-4 py-3">
                                <Link :href="route('members.show', m.member_id)" class="flex items-center gap-3 hover:text-emerald-600 transition">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">{{ m.first_name.charAt(0) }}{{ m.last_name.charAt(0) }}</div>
                                    <span class="font-medium text-gray-900">{{ m.full_name }}</span>
                                </Link>
                            </td>
                            <td v-for="dt in documentTypes.filter(t => t.value !== 'other')" :key="dt.value" class="hidden px-3 py-3 text-center md:table-cell">
                                <span
                                    class="inline-block h-3 w-3 rounded-full cursor-pointer"
                                    :class="statusColors[m.documents.find(d => d.type === dt.value)?.status || 'missing']"
                                    :title="m.documents.find(d => d.type === dt.value)?.status_label || 'Manquant'"
                                    @click="openUpload(m, dt.value)"
                                />
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span v-if="!m.has_issues" class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">OK</span>
                                <span v-else class="rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700">{{ m.expired_count + m.missing_count }} problème{{ (m.expired_count + m.missing_count) > 1 ? 's' : '' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <button class="text-xs text-emerald-600 hover:text-emerald-700" @click="openUpload(m)">Ajouter</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="rounded-xl border border-dashed border-gray-300 p-10 text-center">
                <p class="text-sm text-gray-400">Aucun membre trouvé.</p>
            </div>
        </div>

        <!-- Upload modal -->
        <Modal :show="showUpload" @close="showUpload = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Ajouter un document</h3>
                <p v-if="uploadTarget" class="mt-1 text-sm text-gray-500">Pour {{ uploadTarget.full_name }}</p>

                <form class="mt-5 space-y-4" @submit.prevent="submitUpload">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <select v-model="form.type" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="" disabled>Choisir un type</option>
                            <option v-for="dt in documentTypes" :key="dt.value" :value="dt.value">{{ dt.label }}</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.type" />
                    </div>

                    <div v-if="form.type === 'other'">
                        <label class="block text-sm font-medium text-gray-700">Nom du document</label>
                        <input v-model="form.custom_label" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Ex: Fiche sanitaire" />
                        <InputError class="mt-1" :message="form.errors.custom_label" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fichier</label>
                        <input type="file" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-emerald-700 hover:file:bg-emerald-100" @input="form.file = $event.target.files[0]" />
                        <InputError class="mt-1" :message="form.errors.file" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Saison</label>
                            <input v-model="form.season" type="text" required class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Expire le</label>
                            <input v-model="form.expires_at" type="date" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-emerald-500 focus:ring-emerald-500" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="text-sm text-gray-500 hover:text-gray-700" @click="showUpload = false">Annuler</button>
                        <button type="submit" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-40">Enregistrer</button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
