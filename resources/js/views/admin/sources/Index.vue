<template>
    <div class="sources-page">
        <Card>
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <span>Gestión de Marketplaces (Sources)</span>
                    <div class="flex items-center gap-2">
                        <Button
                            label="Actualizar"
                            icon="pi pi-refresh"
                            size="small"
                            outlined
                            severity="secondary"
                            :loading="isLoading"
                            @click="getSources"
                        />
                        <Button
                            v-if="can('source-create')"
                            label="Nuevo Marketplace"
                            icon="pi pi-plus"
                            size="small"
                            severity="primary"
                            @click="openCreateDialog"
                        />
                    </div>
                </div>
            </template>

            <template #subtitle>
                Administra los orígenes de los productos (Taobao, Weidian, 1688, etc.).
            </template>

            <template #content>
                <DataTable
                    v-model:filters="filters"
                    :value="sources || []"
                    :paginator="true"
                    :rows="10"
                    :rows-per-page-options="[10, 25, 50]"
                    data-key="id"
                    striped-rows
                    size="small"
                    :loading="isLoading"
                    filter-display="menu"
                    :global-filter-fields="['id', 'name', 'slug']"
                >
                    <template #empty>
                        <div class="table-empty-state text-center py-5">
                            <i class="pi pi-inbox text-4xl opacity-20 mb-3 block"></i>
                            <p>No se encontraron marketplaces</p>
                        </div>
                    </template>

                    <Column field="id" header="ID" sortable class="w-[80px]">
                        <template #body="slotProps">
                            <span class="font-mono">#{{ slotProps.data.id }}</span>
                        </template>
                    </Column>

                    <Column field="logo_url" header="Logo" class="w-[100px]">
                        <template #body="slotProps">
                            <img v-if="slotProps.data.logo_url" :src="slotProps.data.logo_url" class="h-8 object-contain" :alt="slotProps.data.name" />
                            <div v-else class="h-8 w-8 bg-slate-100 rounded flex items-center justify-center">
                                <i class="pi pi-image text-slate-400"></i>
                            </div>
                        </template>
                    </Column>

                    <Column field="name" header="Nombre" sortable filter class="min-w-[150px]"></Column>
                    <Column field="slug" header="Slug" sortable filter class="min-w-[150px]"></Column>
                    <Column field="base_url" header="URL Base" class="min-w-[200px]">
                        <template #body="slotProps">
                            <a :href="slotProps.data.base_url" target="_blank" class="text-blue-500 hover:underline text-xs">{{ slotProps.data.base_url || '-' }}</a>
                        </template>
                    </Column>

                    <Column field="created_at" header="Fecha" sortable class="w-[150px]"></Column>

                    <Column header="Acciones" class="w-[120px]">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button
                                    v-if="can('source-edit')"
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    severity="secondary"
                                    size="small"
                                    @click="openEditDialog(slotProps.data)"
                                />
                                <Button
                                    v-if="can('source-delete')"
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeleteSource(slotProps.data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog
            v-model:visible="sourceDialog.open"
            modal
            :header="sourceDialog.type === 'create' ? 'Crear Marketplace' : 'Editar Marketplace'"
            :style="{ width: '500px' }"
        >
            <div class="flex flex-col gap-4 py-2">
                <div class="flex flex-col gap-1">
                    <label for="name" class="font-semibold text-sm">Nombre</label>
                    <InputText v-model="source.name" id="name" :class="{ 'p-invalid': hasError('name') }" @input="updateSlug" />
                    <small v-if="hasError('name')" class="text-red-500">{{ getError('name') }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label for="slug" class="font-semibold text-sm">Slug (URL)</label>
                    <InputText v-model="source.slug" id="slug" :class="{ 'p-invalid': hasError('slug') }" />
                    <small v-if="hasError('slug')" class="text-red-500">{{ getError('slug') }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label for="logo_url" class="font-semibold text-sm">URL Logo</label>
                    <InputText v-model="source.logo_url" id="logo_url" />
                </div>
                <div class="flex flex-col gap-1">
                    <label for="base_url" class="font-semibold text-sm">URL Base</label>
                    <InputText v-model="source.base_url" id="base_url" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancelar" text severity="secondary" @click="closeDialog" />
                <Button v-if="sourceDialog.type === 'create'" label="Crear" @click="submitCreate" :loading="isLoading" />
                <Button v-else label="Guardar" @click="submitUpdate" :loading="isLoading" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, inject } from "vue";
import useSources from "@/composables/sources";
import { useAbility } from '@casl/vue';
import { FilterMatchMode } from "@primevue/core/api";

const { sources, source, getSources, createSource, updateSource, deleteSource, resetSource, setSource, hasError, getError, upsertSourceRecord, isLoading } = useSources();
const { can } = useAbility();
const swal = inject('$swal');

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const sourceDialog = reactive({
    open: false,
    type: 'create'
});

const updateSlug = () => {
    if (sourceDialog.type === 'create') {
        source.value.slug = source.value.name
            .toLowerCase()
            .replace(/ /g, '-')
            .replace(/[^\w-]+/g, '');
    }
};

const openCreateDialog = () => {
    resetSource();
    sourceDialog.type = 'create';
    sourceDialog.open = true;
};

const openEditDialog = (data) => {
    setSource(data);
    sourceDialog.type = 'edit';
    sourceDialog.open = true;
};

const closeDialog = () => {
    sourceDialog.open = false;
};

const submitCreate = async () => {
    const data = await createSource();
    if (data) {
        upsertSourceRecord(data);
        closeDialog();
    }
};

const submitUpdate = async () => {
    const data = await updateSource();
    if (data) {
        upsertSourceRecord(data);
        closeDialog();
    }
};

const confirmDeleteSource = (data) => {
    swal({
        title: '¿Eliminar marketplace?',
        text: `El marketplace "${data.name}" se eliminará permanentemente.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteSource(data.id);
        }
    });
};

onMounted(() => {
    getSources();
});
</script>
