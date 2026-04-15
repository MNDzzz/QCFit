<template>
    <div class="products-page">
        <Card>
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <span>Gestión de Productos</span>
                    <div class="flex items-center gap-2">
                        <Button
                            label="Actualizar"
                            icon="pi pi-refresh"
                            size="small"
                            outlined
                            severity="secondary"
                            :loading="isLoading"
                            @click="fetchProducts"
                        />
                        <Button
                            v-if="can('product-create')"
                            label="Nuevo Producto"
                            icon="pi pi-plus"
                            size="small"
                            severity="primary"
                            @click="openCreateDialog"
                        />
                    </div>
                </div>
            </template>

            <template #subtitle>
                Catálogo global de productos indexados en QCFit.
            </template>

            <template #content>
                <DataTable
                    v-model:filters="filters"
                    :value="products || []"
                    :paginator="true"
                    :rows="10"
                    :rows-per-page-options="[10, 25, 50]"
                    data-key="id"
                    striped-rows
                    size="small"
                    :loading="isLoading"
                    filter-display="menu"
                    :global-filter-fields="['id', 'name', 'external_id']"
                >
                    <template #empty>
                        <div class="text-center py-5">
                            <i class="pi pi-inbox text-4xl opacity-20 mb-3 block"></i>
                            <p>No se encontraron productos</p>
                        </div>
                    </template>

                    <Column field="id" header="ID" sortable class="w-[80px]">
                        <template #body="slotProps">
                            <span class="font-mono">#{{ slotProps.data.id }}</span>
                        </template>
                    </Column>

                    <Column header="Imagen" class="w-[100px]">
                        <template #body="slotProps">
                            <div class="h-12 w-12 rounded overflow-hidden shadow-sm border border-gray-100 bg-slate-50 flex items-center justify-center">
                                <img v-if="slotProps.data.thumbnail" :src="slotProps.data.thumbnail" class="h-full w-full object-cover" />
                                <i v-else class="pi pi-image text-slate-300"></i>
                            </div>
                        </template>
                    </Column>

                    <Column field="name" header="Nombre" sortable filter class="min-w-[200px]">
                        <template #body="slotProps">
                            <div class="flex flex-col">
                                <span class="font-medium text-slate-700">{{ slotProps.data.name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ slotProps.data.external_id }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column field="category.name" header="Categoría" sortable class="min-w-[120px]">
                        <template #body="slotProps">
                            <Tag v-if="slotProps.data.category" :value="slotProps.data.category.name" severity="secondary" />
                            <span v-else class="text-slate-300">-</span>
                        </template>
                    </Column>

                    <Column field="brand.name" header="Marca" sortable class="min-w-[120px]">
                        <template #body="slotProps">
                            <span v-if="slotProps.data.brand" class="font-semibold">{{ slotProps.data.brand.name }}</span>
                            <span v-else class="text-slate-300">-</span>
                        </template>
                    </Column>

                    <Column field="source.name" header="Origen" class="w-[120px]">
                        <template #body="slotProps">
                             <div v-if="slotProps.data.source" class="flex items-center gap-2">
                                <img v-if="slotProps.data.source.logo_url" :src="slotProps.data.source.logo_url" class="h-4" />
                                <span class="text-xs uppercase">{{ slotProps.data.source.name }}</span>
                             </div>
                        </template>
                    </Column>

                    <Column header="Acciones" class="w-[120px]">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button
                                    v-if="can('product-edit')"
                                    icon="pi pi-pencil"
                                    text
                                    rounded
                                    severity="secondary"
                                    size="small"
                                    @click="openEditDialog(slotProps.data)"
                                />
                                <Button
                                    v-if="can('product-delete')"
                                    icon="pi pi-trash"
                                    text
                                    rounded
                                    severity="danger"
                                    size="small"
                                    @click="confirmDeleteProduct(slotProps.data)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <!-- Dialogo de Creación/Edición -->
        <Dialog
            v-model:visible="productDialog.open"
            modal
            :header="productDialog.type === 'create' ? 'Nuevo Producto' : 'Editar Producto'"
            :style="{ width: '650px' }"
        >
            <div class="grid grid-cols-2 gap-4 py-4">
                <div class="col-span-2 flex flex-col gap-1">
                    <label class="font-semibold text-sm">Nombre del Producto</label>
                    <InputText v-model="product.name" :class="{ 'p-invalid': hasError('name') }" />
                    <small v-if="hasError('name')" class="text-red-500">{{ getError('name') }}</small>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-sm">ID Marketplace (External ID)</label>
                    <InputText v-model="product.external_id" placeholder="Ej: 6824563" />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-sm">Categoría</label>
                    <Select
                        v-model="product.category_id"
                        :options="categoryList"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Seleccionar..."
                        class="w-full"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-sm">Marca</label>
                    <Select
                        v-model="product.brand_id"
                        :options="brandList"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Seleccionar..."
                        class="w-full"
                    />
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-sm">Marketplace (Origen)</label>
                    <Select
                        v-model="product.source_id"
                        :options="sourceList"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Seleccionar..."
                        class="w-full"
                    />
                </div>

                <div class="col-span-2 flex flex-col gap-1">
                    <label class="font-semibold text-sm">Enlace Original</label>
                    <InputText v-model="product.original_link" placeholder="https://taobao.com/item/..." />
                </div>
            </div>

            <template #footer>
                <Button label="Cancelar" text severity="secondary" @click="productDialog.open = false" />
                <Button label="Guardar" @click="saveProduct" :loading="isLoading" />
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, inject } from "vue";
import useProducts from "@/composables/products";
import useCategories from "@/composables/categories";
import useBrands from "@/composables/brands";
import useSources from "@/composables/sources";
import { useAbility } from '@casl/vue';
import { FilterMatchMode } from "@primevue/core/api";

const { products, product, getProducts, createProduct, updateProduct, deleteProduct, resetProduct, setProduct, hasError, getError, isLoading } = useProducts();
const { categoryList, getCategoryList } = useCategories();
const { brandList, getBrandList } = useBrands();
const { sourceList, getSourceList } = useSources();

const { can } = useAbility();
const swal = inject('$swal');

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const productDialog = reactive({
    open: false,
    type: 'create'
});

const fetchProducts = () => getProducts();

const openCreateDialog = () => {
    resetProduct();
    productDialog.type = 'create';
    productDialog.open = true;
};

const openEditDialog = (data) => {
    setProduct(data);
    productDialog.type = 'edit';
    productDialog.open = true;
};

const saveProduct = async () => {
    const success = productDialog.type === 'create' 
        ? await createProduct() 
        : await updateProduct();
    
    if (success) {
        productDialog.open = false;
        fetchProducts();
    }
};

const confirmDeleteProduct = (data) => {
    swal({
        title: '¿Eliminar producto?',
        text: `"${data.name}" se eliminará del sistema.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteProduct(data.id);
        }
    });
};

onMounted(() => {
    fetchProducts();
    getCategoryList();
    getBrandList();
    getSourceList();
});
</script>
