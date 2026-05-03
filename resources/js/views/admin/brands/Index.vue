<template>
    <div class="brands-page">
        <Card>
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <span>Brand Management</span>
                    <div class="flex items-center gap-2">
                        <Button
                            label="Refresh"
                            icon="pi pi-refresh"
                            size="small"
                            outlined
                            severity="secondary"
                            :loading="isLoading"
                            @click="getBrands"
                        />
                        <Button
                            v-if="can('brand-create')"
                            label="New Brand"
                            icon="pi pi-plus"
                            size="small"
                            severity="primary"
                            @click="openCreateDialog"
                        />
                    </div>
                </div>
            </template>

            <template #subtitle>
                Manage the product brands (Nike, Adidas, Stussy, etc.).
            </template>

            <template #content>
                <DataTable
                    v-model:filters="filters"
                    :value="brands || []"
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
                            <p>No brands found</p>
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
                                <i class="pi pi-tag text-slate-400"></i>
                            </div>
                        </template>
                    </Column>

                    <Column field="name" header="Name" sortable filter class="min-w-[150px]"></Column>
                    <Column field="slug" header="Slug" sortable filter class="min-w-[150px]"></Column>
                    
                    <Column field="description" header="Description" class="min-w-[200px]">
                        <template #body="slotProps">
                            <span class="text-xs text-slate-500 line-clamp-1">{{ slotProps.data.description || '-' }}</span>
                        </template>
                    </Column>

                    <Column field="created_at" header="Date" sortable class="w-[150px]"></Column>

                    <Column header="Actions" class="w-[120px]">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button
                                    v-if="can('brand-edit')"
                                    icon="pi pi-box"
                                    text
                                    rounded
                                    severity="info"
                                    size="small"
                                    v-tooltip.top="'View Products'"
                                    @click="openProductsDialog(slotProps.data)"
                                />
                                <Button
                                    v-if="can('brand-edit')"
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    severity="secondary"
                                    size="small"
                                    @click="openEditDialog(slotProps.data)"
                                />
                                <Button
                                    v-if="can('brand-delete')"
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeleteBrand(slotProps.data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <Dialog
            v-model:visible="brandDialog.open"
            modal
            :header="brandDialog.type === 'create' ? 'Create Brand' : 'Edit Brand'"
            :style="{ width: '500px' }"
        >
            <div class="flex flex-col gap-4 py-2">
                <div class="flex flex-col gap-1">
                    <label for="name" class="font-semibold text-sm">Name</label>
                    <InputText v-model="brand.name" id="name" :class="{ 'p-invalid': hasError('name') }" @input="updateSlug" />
                    <small v-if="hasError('name')" class="text-red-500">{{ getError('name') }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label for="slug" class="font-semibold text-sm">Slug (URL)</label>
                    <InputText v-model="brand.slug" id="slug" :class="{ 'p-invalid': hasError('slug') }" />
                    <small v-if="hasError('slug')" class="text-red-500">{{ getError('slug') }}</small>
                </div>
                <div class="flex flex-col gap-1">
                    <label for="logo_url" class="font-semibold text-sm">URL Logo</label>
                    <InputText v-model="brand.logo_url" id="logo_url" />
                </div>
                <div class="flex flex-col gap-1">
                    <label for="description" class="font-semibold text-sm">Description</label>
                    <Textarea v-model="brand.description" id="description" rows="3" class="w-full" />
                </div>
            </div>
            <template #footer>
                <Button label="Cancel" text severity="secondary" @click="closeDialog" />
                <Button v-if="brandDialog.type === 'create'" label="Create" @click="submitCreate" :loading="isLoading" />
                <Button v-else label="Save" @click="submitUpdate" :loading="isLoading" />
            </template>
        </Dialog>

        <!-- Diálogo para ver productos asociados -->
        <Dialog 
            v-model:visible="productsDialog.open" 
            modal 
            :header="'Products of ' + productsDialog.brandName" 
            :style="{ width: '800px' }"
        >
            <DataTable 
                :value="associatedProducts" 
                size="small" 
                :paginator="true" 
                :rows="5"
                striped-rows
            >
                <template #empty>
                    <div class="text-center py-4 text-slate-400">This brand has no associated products.</div>
                </template>

                <Column field="id" header="ID" class="w-[60px]"></Column>
                <Column header="Image" class="w-[80px]">
                    <template #body="slotProps">
                        <img v-if="slotProps.data.thumbnail" :src="slotProps.data.thumbnail" class="h-8 w-8 object-cover rounded" />
                    </template>
                </Column>
                <Column field="name" header="Name" sortable></Column>
                
                <Column header="Reassign Brand" class="w-[220px]">
                    <template #body="slotProps">
                        <div class="flex items-center gap-2">
                            <Select 
                                v-model="slotProps.data.temp_brand_id" 
                                :options="brandList" 
                                optionLabel="name" 
                                optionValue="id" 
                                placeholder="Move to..." 
                                class="w-full text-xs"
                                @change="onReassignBrand(slotProps.data)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>
            <template #footer>
                <Button label="Close" severity="secondary" @click="productsDialog.open = false" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, inject } from "vue";
import useBrands from "@/composables/brands";
import { useAbility } from '@casl/vue';
import { FilterMatchMode } from "@primevue/core/api";

const { 
    brands, brand, getBrands, createBrand, updateBrand, deleteBrand, 
    resetBrand, setBrand, hasError, getError, upsertBrandRecord, 
    isLoading, getBrandProducts, updateProductBrand, getBrandList, brandList 
} = useBrands();
const { can } = useAbility();
const swal = inject('$swal');

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const brandDialog = reactive({
    open: false,
    type: 'create'
});

const productsDialog = reactive({
    open: false,
    brandId: null,
    brandName: ''
});

const associatedProducts = ref([]);

const updateSlug = () => {
    if (brandDialog.type === 'create') {
        brand.value.slug = brand.value.name
            .toLowerCase()
            .replace(/ /g, '-')
            .replace(/[^\w-]+/g, '');
    }
};

const openCreateDialog = () => {
    resetBrand();
    brandDialog.type = 'create';
    brandDialog.open = true;
};

const openEditDialog = (data) => {
    setBrand(data);
    brandDialog.type = 'edit';
    brandDialog.open = true;
};

const closeDialog = () => {
    brandDialog.open = false;
};

const openProductsDialog = async (data) => {
    productsDialog.brandId = data.id;
    productsDialog.brandName = data.name;
    const response = await getBrandProducts(data.id);
    // Añadimos temp_brand_id para el select de cada fila
    associatedProducts.value = response.map(p => ({
        ...p,
        temp_brand_id: data.id
    }));
    productsDialog.open = true;
};

const onReassignBrand = async (productData) => {
    if (productData.temp_brand_id === productsDialog.brandId) return;

    swal({
        title: 'Reassign product?',
        text: `The product will be moved from "${productsDialog.brandName}" to the selected brand.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, move',
        cancelButtonText: 'Cancel'
    }).then(async (result) => {
        if (result.isConfirmed) {
            const success = await updateProductBrand(
                productsDialog.brandId, 
                productData.id, 
                productData.temp_brand_id
            );
            if (success) {
                // Quitamos el producto de la lista actual
                associatedProducts.value = associatedProducts.value.filter(p => p.id !== productData.id);
            }
        } else {
            // Revertimos el select si cancela
            productData.temp_brand_id = productsDialog.brandId;
        }
    });
};

const submitCreate = async () => {
    const data = await createBrand();
    if (data) {
        upsertBrandRecord(data);
        closeDialog();
    }
};

const submitUpdate = async () => {
    const data = await updateBrand();
    if (data) {
        upsertBrandRecord(data);
        closeDialog();
    }
};

const confirmDeleteBrand = (data) => {
    swal({
        title: 'Delete brand?',
        text: `The brand "${data.name}" will be permanently deleted.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ef4444'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteBrand(data.id);
        }
    });
};

onMounted(() => {
    getBrands();
    getBrandList();
});
</script>
