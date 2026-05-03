<template>
    <div class="products-page">
        <Card>
            <template #title>
                <div class="flex items-center justify-between w-full">
                    <span>Product Management</span>
                    <div class="flex items-center gap-2">
                        <Button
                            label="Refresh"
                            icon="pi pi-refresh"
                            size="small"
                            outlined
                            severity="secondary"
                            :loading="isLoading"
                            @click="fetchProducts"
                        />
                        <Button
                            v-if="can('product-create')"
                            label="New Product"
                            icon="pi pi-plus"
                            size="small"
                            severity="primary"
                            @click="openCreateDialog"
                        />
                    </div>
                </div>
            </template>

            <template #subtitle>
                Global product catalog indexed in QCFit.
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
                            <p>No products found</p>
                        </div>
                    </template>

                    <Column field="id" header="ID" sortable class="w-[80px]">
                        <template #body="slotProps">
                            <span class="font-mono">#{{ slotProps.data.id }}</span>
                        </template>
                    </Column>

                    <Column header="Image" class="w-[100px]">
                        <template #body="slotProps">
                            <div class="h-12 w-12 rounded overflow-hidden shadow-sm border border-gray-100 bg-slate-50 flex items-center justify-center">
                                <img v-if="slotProps.data.thumbnail" :src="slotProps.data.thumbnail" class="h-full w-full object-cover" />
                                <i v-else class="pi pi-image text-slate-300"></i>
                            </div>
                        </template>
                    </Column>

                    <Column field="name" header="Name" sortable filter class="min-w-[200px]">
                        <template #body="slotProps">
                            <div class="flex flex-col">
                                <span class="font-medium text-slate-700">{{ slotProps.data.name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ slotProps.data.external_id }}</span>
                            </div>
                        </template>
                    </Column>

                    <Column field="category.name" header="Category" sortable class="min-w-[120px]">
                        <template #body="slotProps">
                            <Tag v-if="slotProps.data.category" :value="slotProps.data.category.name" severity="secondary" />
                            <span v-else class="text-slate-300">-</span>
                        </template>
                    </Column>

                    <Column field="brand.name" header="Brand" sortable class="min-w-[120px]">
                        <template #body="slotProps">
                            <span v-if="slotProps.data.brand" class="font-semibold">{{ slotProps.data.brand.name }}</span>
                            <span v-else class="text-slate-300">-</span>
                        </template>
                    </Column>

                    <Column field="source.name" header="Source" class="w-[120px]">
                        <template #body="slotProps">
                             <div v-if="slotProps.data.source" class="flex items-center gap-2">
                                <img v-if="slotProps.data.source.logo_url" :src="slotProps.data.source.logo_url" class="h-4" />
                                <span class="text-xs uppercase">{{ slotProps.data.source.name }}</span>
                             </div>
                        </template>
                    </Column>

                    <Column header="Actions" class="w-[120px]">
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

        <!-- Create/Edit Dialog -->
        <Dialog
            v-model:visible="productDialog.open"
            modal
            :header="productDialog.type === 'create' ? 'New Product' : 'Edit Product'"
            :style="{ width: '700px' }"
        >
            <div class="flex flex-col gap-5 py-4">
                <!-- Product Name -->
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-sm">Product Name <span class="text-red-500">*</span></label>
                    <InputText v-model="product.name" :class="{ 'p-invalid': hasError('name') }" placeholder="e.g. Nike Air Force 1 White" />
                    <small v-if="hasError('name')" class="text-red-500">{{ getError('name') }}</small>
                </div>

                <!-- Two columns -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-sm">Category</label>
                        <Select
                            v-model="product.category_id"
                            :options="categoryList"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Select..."
                            class="w-full"
                        />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-sm">Brand</label>
                        <Select
                            v-model="product.brand_id"
                            :options="brandList"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Select..."
                            class="w-full"
                        />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-sm">Marketplace (Source)</label>
                        <Select
                            v-model="product.source_id"
                            :options="sourceList"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Select..."
                            class="w-full"
                        />
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-semibold text-sm">External ID</label>
                        <InputText v-model="product.external_id" placeholder="e.g. 6824563" />
                    </div>
                </div>

                <!-- Original Link -->
                <div class="flex flex-col gap-1">
                    <label class="font-semibold text-sm">Original Link</label>
                    <InputText v-model="product.original_link" placeholder="https://weidian.com/item/..." />
                </div>

                <!-- Existing Images (edit mode) -->
                <div v-if="productDialog.type === 'edit' && product.images?.length" class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">Current Images</label>
                    <div class="flex flex-wrap gap-3">
                        <div
                            v-for="img in product.images"
                            :key="img.id"
                            class="relative group"
                        >
                            <div
                                class="h-20 w-20 rounded-lg overflow-hidden border-2 transition-all"
                                :class="product.remove_image_ids?.includes(img.id) ? 'border-red-400 opacity-40' : 'border-slate-200'"
                            >
                                <img :src="img.url" class="h-full w-full object-cover" />
                            </div>
                            <button
                                type="button"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow-md hover:bg-red-600 transition-colors"
                                @click="toggleRemoveImage(img.id)"
                                :title="product.remove_image_ids?.includes(img.id) ? 'Undo remove' : 'Remove image'"
                            >
                                <i :class="product.remove_image_ids?.includes(img.id) ? 'pi pi-undo' : 'pi pi-times'" style="font-size: 0.6rem"></i>
                            </button>
                            <span class="text-[9px] text-center block mt-1 text-slate-400">{{ img.type }}</span>
                        </div>
                    </div>
                </div>

                <!-- Upload New Images -->
                <div class="flex flex-col gap-2">
                    <label class="font-semibold text-sm">
                        {{ productDialog.type === 'create' ? 'Product Images' : 'Add More Images' }}
                    </label>
                    <div
                        class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center cursor-pointer hover:border-violet-400 hover:bg-violet-50/50 transition-all"
                        @click="triggerFileInput"
                        @dragover.prevent="dragOver = true"
                        @dragleave.prevent="dragOver = false"
                        @drop.prevent="handleDrop"
                        :class="{ 'border-violet-400 bg-violet-50/50': dragOver }"
                    >
                        <i class="pi pi-cloud-upload text-3xl text-slate-400 mb-2"></i>
                        <p class="text-sm text-slate-500">Click or drag & drop images here</p>
                        <p class="text-xs text-slate-400 mt-1">JPEG, PNG, WebP, GIF — Max 5MB each</p>
                    </div>
                    <input
                        ref="fileInput"
                        type="file"
                        multiple
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        class="hidden"
                        @change="handleFileSelect"
                    />

                    <!-- Preview selected files -->
                    <div v-if="previewFiles.length" class="flex flex-wrap gap-3 mt-2">
                        <div v-for="(preview, index) in previewFiles" :key="index" class="relative group">
                            <div class="h-20 w-20 rounded-lg overflow-hidden border-2 border-violet-300">
                                <img :src="preview.url" class="h-full w-full object-cover" />
                            </div>
                            <button
                                type="button"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow-md hover:bg-red-600 transition-colors"
                                @click="removePreviewFile(index)"
                            >
                                <i class="pi pi-times" style="font-size: 0.6rem"></i>
                            </button>
                            <span class="text-[9px] text-center block mt-1 text-slate-500 truncate w-20">{{ preview.name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" text severity="secondary" @click="productDialog.open = false" />
                <Button label="Save" @click="saveProduct" :loading="isLoading" />
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

const fileInput = ref(null);
const previewFiles = ref([]);
const dragOver = ref(false);

const fetchProducts = () => getProducts();

const openCreateDialog = () => {
    resetProduct();
    previewFiles.value = [];
    productDialog.type = 'create';
    productDialog.open = true;
};

const openEditDialog = (data) => {
    setProduct(data);
    previewFiles.value = [];
    productDialog.type = 'edit';
    productDialog.open = true;
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const handleFileSelect = (event) => {
    addFiles(Array.from(event.target.files));
    // Reset input so same file can be selected again
    event.target.value = '';
};

const handleDrop = (event) => {
    dragOver.value = false;
    const files = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
    addFiles(files);
};

const addFiles = (files) => {
    files.forEach(file => {
        if (file.size > 5 * 1024 * 1024) return; // Skip files > 5MB
        previewFiles.value.push({
            file,
            name: file.name,
            url: URL.createObjectURL(file)
        });
    });
    // Sync with product model
    product.value.images_upload = previewFiles.value.map(p => p.file);
};

const removePreviewFile = (index) => {
    URL.revokeObjectURL(previewFiles.value[index].url);
    previewFiles.value.splice(index, 1);
    product.value.images_upload = previewFiles.value.map(p => p.file);
};

const toggleRemoveImage = (imageId) => {
    if (!product.value.remove_image_ids) {
        product.value.remove_image_ids = [];
    }
    const idx = product.value.remove_image_ids.indexOf(imageId);
    if (idx >= 0) {
        product.value.remove_image_ids.splice(idx, 1);
    } else {
        product.value.remove_image_ids.push(imageId);
    }
};

const saveProduct = async () => {
    const success = productDialog.type === 'create' 
        ? await createProduct() 
        : await updateProduct();
    
    if (success) {
        productDialog.open = false;
        previewFiles.value = [];
        fetchProducts();
    }
};

const confirmDeleteProduct = (data) => {
    swal({
        title: 'Delete product?',
        text: `"${data.name}" will be removed from the system.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel',
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
